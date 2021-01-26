<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

abstract class arElasticSearchModelBase
{
  protected
    $timer = null,
    $count = 0;

  protected static
    $conn,
    $termParentList,
    $allowedLanguages;

  public function __construct()
  {
    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->search = QubitSearch::getInstance();

    $this->log(" - Loading " . get_class($this) . "...");
  }

  public function getCount()
  {
    return $this->count;
  }

  public function setCount($count)
  {
    $this->count = $count;
  }

  public function setTimer($timer)
  {
    $this->timer = $timer;
  }

  protected function log($message)
  {
    $this->search->log($message);
  }

  protected function logEntry($title, $count)
  {
    $this->log(sprintf('    [%s] %s inserted (%ss) (%s/%s)',
      str_replace('arElasticSearch', '', get_class($this)),
      $title,
      $this->timer->elapsed(),
      $count,
      $this->getCount()));
  }

  public static function serializeI18ns($id, array $classes, $options = array())
  {
    if (empty($classes))
    {
      throw new sfException('At least one class name must be passed.');
    }

    // Get an array of i18n languages
    if (!isset(self::$allowedLanguages))
    {
      self::$allowedLanguages = sfConfig::get('app_i18n_languages');
    }

    // Properties
    $i18ns = array('languages' => array());

    // Allow merging i18n fields, used for partial foreign types
    // when different object fields are included in the same object
    if (isset($options['merge']))
    {
      $i18ns = $options['merge'];
    }

    foreach ($classes as $class)
    {
      // Build SQL query per table. Tried with joins but for some reason the
      // culture value appears empty. The statement can't be reused as it's
      // not possible to bind and use a variable for the table name.
      $rows = QubitPdo::fetchAll(
        sprintf('SELECT * FROM %s WHERE id = ?', ($class.'I18n')::TABLE_NAME),
        array($id),
        array('fetchMode' => PDO::FETCH_ASSOC)
      );

      foreach ($rows as $row)
      {
        // Any i18n record within a culture previously not configured will
        // be ignored since the search engine will only accept known languages
        if (!in_array($row['culture'], self::$allowedLanguages))
        {
          continue;
        }

        // Collect cultures added
        $i18ns['languages'][] = $row['culture'];

        foreach ($row as $key => $value)
        {
          // Pass if the column is unneeded or null, or if it's not set in options fields
          if (in_array($key, array('id', 'culture')) || is_null($value)
            || (isset($options['fields']) && !in_array($key, $options['fields'])))
          {
            continue;
          }

          $camelized = lcfirst(sfInflector::camelize($key));
          $i18ns[$row['culture']][$camelized] = $value;
        }
      }
    }

    // Remove duplicated cultures from language values
    $i18ns['languages'] = array_unique(array_values($i18ns['languages']));

    return $i18ns;
  }

  # abstract public function update($object);
  public static function update($object)
  {
    return true;
  }

  /**
   * Add boost values to various fields.
   *
   * @param array &$fields  An array of the fields to be modified with their boost values added.
   * @param array $i18nfields  Specifies which i18n fields to boost, and which boost value to use.
   *                           The array is in the form of 'fieldName' => (int)boostNumber
   *
   * @param array $nonI18nFields  Same as above, except for non-i18n string fields.
   */
  protected static function addBoostValuesToFields(&$fields, $i18nFields, $nonI18nFields)
  {
    // Expand all the i18n fields into their various cultures, add boost values
    $i18nBoostFields = arElasticSearchPluginUtil::getI18nFieldNames(
      array_keys($i18nFields),
      null,
      $i18nFields
    );

    foreach ($fields as &$field)
    {
      foreach ($i18nBoostFields as $i18nBoostField)
      {
        // Match boost field against current field, add boost if match found.
        // i.e.: i18n.en.title will turn into i18n.en.title^10
        if (0 === strpos($i18nBoostField, $field))
        {
          $field = $i18nBoostField;
        }
      }

      foreach ($nonI18nFields as $nonI18nBoostField => $boost)
      {
        $nonI18nBoostField = $nonI18nBoostField.'^'.$boost;

        if (0 === strpos($nonI18nBoostField, $field))
        {
          $field = $nonI18nBoostField;
        }
      }
    }
  }

  public static function getRelatedTerms($objectId, $taxonomyIds)
  {
    // We can't reuse this statement as there is no way to bind
    // arrays with unknown length to use them in an IN condition.
    $sql  = 'SELECT term.taxonomy_id, term.id
      FROM object_term_relation otr
      JOIN term ON otr.term_id = term.id
      WHERE otr.object_id = ?
      AND term.taxonomy_id IN ('.implode(',', $taxonomyIds).')';

    // Use FETCH_GROUP and FETCH_COLUMN combined to get
    // an array of term ids grouped by taxonomy.
    return QubitPdo::fetchAll(
      $sql,
      array($objectId),
      array('fetchMode' => PDO::FETCH_GROUP|PDO::FETCH_COLUMN)
    );
  }

  public static function extendRelatedTerms($termIds)
  {
    if (empty($termIds))
    {
      return array();
    }

    // Try to get term parent list from sfConfig, added in
    // the populate process when it includes terms and/or IOs.
    if (!isset(self::$termParentList))
    {
      self::$termParentList = sfConfig::get('term_parent_list', null);
    }

    // If the term parent list is populated, recursively extend the terms
    if (isset(self::$termParentList))
    {
      $relatedTerms = array();

      // Iterate over each directly related term, adding all ancestors of each
      foreach($termIds as $id)
      {
        $relatedTerms = array_merge(
          $relatedTerms,
          self::recursivelyGetParentTerms($id)
        );
      }

      return array_unique($relatedTerms);
    }

    // Otherwise, get the extended terms from the database.
    // We can't reuse this statement as there is no way to bind
    // arrays with unknown length to use them in an IN condition.
    $sql = 'WITH RECURSIVE cte AS
    	(
    	  SELECT term1.id, term1.parent_id
        FROM term term1 WHERE term1.id IN ('.implode(',', $termIds).')
    	  UNION ALL
    	  SELECT term2.id, term2.parent_id
        FROM term term2 JOIN cte ON cte.parent_id=term2.id
        WHERE term2.id != ?
    	)
    	SELECT DISTINCT id FROM cte';

    return QubitPdo::fetchAll(
      $sql,
      array(QubitTerm::ROOT_ID),
      array('fetchMode' => PDO::FETCH_COLUMN)
    );
  }

  /**
   * Recursively find all ancestors (except the root) for a term.
   *
   * @param array $id  The term id to find the ancestors for.
   * @return array  Ids of the ancestors and self.
   */
  private static function recursivelyGetParentTerms($id)
  {
    if (!isset(self::$termParentList) || null === $parent = self::$termParentList[$id])
    {
      return array($id);
    }

    return array_merge(array($id), self::recursivelyGetParentTerms($parent));
  }
}
