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

/**
 * Lightweight version of QubitActor which uses PDO directly instead of the Propel ORM
 *
 * @package    arElasticSearchPlugin
 * @author     MJ Suhonos <mj@artefactual.com>
 */
class QubitPdoActor
{
  public
//      $ancestors,
    $i18ns;

  protected
    $data = array();

  protected static
    $conn,
    $lookups,
    $statements;

  /**
   * METHODS
   */
  public function __construct($id, $options = array())
  {
    if (isset($options['conn']))
    {
      self::$conn = $options['conn'];
    }

    if (!isset(self::$conn))
    {
      self::$conn = Propel::getConnection();
    }

    $this->loadData($id, $options);
/*
    // Get inherited ancestors
    if (isset($options['ancestors']))
    {
      $this->ancestors = $options['ancestors'];
    }

    // Get inherited repository, unless a repository is set at current level
    if (isset($options['repository']) && !$this->__isset('repository_id'))
    {
      $this->repository = $options['repository'];
    }
*/
  }

  public function __isset($name)
  {
    return isset($this->data[$name]);
  }

  public function __get($name)
  {
    if ('events' == $name && !isset($this->data[$name]))
    {
      $this->data[$name] = $this->getEvents();
    }

    if (isset($this->data[$name]))
    {
      return $this->data[$name];
    }
  }

  public function __set($name, $value)
  {
    $this->data[$name] = $value;
  }

  protected function loadData($id)
  {
    if (!isset(self::$statements['actor']))
    {
      $sql = 'SELECT
                actor.*,
                slug.slug,
                object.created_at,
                object.updated_at
              FROM '.QubitActor::TABLE_NAME.' actor
              JOIN '.QubitSlug::TABLE_NAME.' slug
                ON actor.id = slug.object_id
              JOIN '.QubitObject::TABLE_NAME.' object
                ON actor.id = object.id
              WHERE actor.id = :id';

      self::$statements['actor'] = self::$conn->prepare($sql);
    }

    // Do select
    self::$statements['actor']->execute(array(
      ':id' => $id));

    // Get first result
    $this->data = self::$statements['actor']->fetch(PDO::FETCH_ASSOC);

    if (false === $this->data)
    {
      throw new sfException("Couldn't find actor (id:'.$this->id.')");
    }

    self::$statements['actor']->closeCursor();

    return $this;
  }

  /**
   * Return an array of i18n arrays
   *
   * @return array of i18n arrays
   */
  public function getI18ns()
  {
    if (!isset($this->i18ns))
    {
      // Find i18ns
      $sql = 'SELECT
                i18n.*
            FROM '.QubitActorI18n::TABLE_NAME.' i18n
            WHERE i18n.id = ?
            ORDER BY i18n.culture';

      $this->i18ns = QubitPdo::fetchAll($sql, array($this->id));
    }

    return $this->i18ns;
  }

  // Serialize yaself!  Don' disrespec yaself
  public function serialize()
  {
    $serialized = array();

    $serialized['slug'] = $this->slug;
    $serialized['entityTypeId'] = $this->entity_type_id;

    // get all i18n-ized versions of this object
    $this->getI18ns();
    foreach ($this->i18ns as $objectI18n)
    {
      // index all values on the i18n-ized object
      foreach (QubitMapping::getI18nFields('QubitActor') as $camelName)
      {
        $fieldName = sfInflector::underscore($camelName);

        if (!is_null($objectI18n->$fieldName))
        {
          $doc[lcfirst($camelName)] = $objectI18n->$fieldName;
        }
      }
      $doc['culture'] = $objectI18n->culture;
      $i18ns[] = $doc;
    }

    $serialized['sourceCulture'] = $this->source_culture;
    $serialized['i18n'] = $i18ns;

    $serialized['createdAt'] = Elastica_Util::convertDate($this->created_at);
    $serialized['updatedAt'] = Elastica_Util::convertDate($this->updated_at);

    return $serialized;
  }
}
