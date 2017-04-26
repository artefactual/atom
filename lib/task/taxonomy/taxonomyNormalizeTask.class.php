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
 * Normalize taxonomy
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class taxonomyNormalizeTask extends arBaseTask
{
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('taxonomy-name', sfCommandArgument::REQUIRED, 'The name of the taxonomy to normalize')
    ));

    $this->addOptions(array(
      new sfCommandOption(
        'application',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'The application name', 'qubit'
      ),
      new sfCommandOption(
        'env',
        null,
        sfCommandOption::PARAMETER_REQUIRED,
        'The environment',
        'cli'
      ),
      new sfCommandOption(
        'culture',
        null,
        sfCommandOption::PARAMETER_OPTIONAL,
        'The name of the culture to normalize (defaults to "en")',
        'en'
      )
    ));

    $this->namespace        = 'taxonomy';
    $this->name             = 'normalize';
    $this->briefDescription = 'Normalize taxonomy terms';
    $this->detailedDescription = <<<EOF
Normalize taxonomy terms
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);

    // Look up taxonomy ID using name
    $this->taxonomyId = $this->getTaxonomyIdByName($arguments['taxonomy-name'], $options['culture']);
    if (!$this->taxonomyId)
    {
      throw new sfException("A taxonomy named '". $arguments['taxonomy-name'] ."' not found for culture '". $options['culture'] ."'.");
    }

    $this->log("Normalizing for '". $options['culture'] ."' culture...");

    // Determine taxonomy term usage then normalize
    $names = array();
    $affectedObjects = array();
    $this->populateTaxonomyNameUsage($names, $options['culture']);
    $this->normalizeTaxonomy($names, $affectedObjects);
    $this->reindexAffectedObjects($affectedObjects);

    $this->log("Affected objects have been reindexed.");
  }

  protected function getTaxonomyIdByName($name, $culture)
  {
    $sql = "SELECT id FROM taxonomy_i18n \r
      WHERE culture=? \r
      AND name=?";

    $statement = QubitFlatfileImport::sqlQuery($sql, array($culture, $name));

    if ($object = $statement->fetch(PDO::FETCH_OBJ))
    {
      return $object->id;
    }
    else
    {
      return false;
    }
  }

  protected function populateTaxonomyNameUsage(&$names, $culture)
  {
    $sql = "SELECT t.id, i.name FROM term t
      INNER JOIN term_i18n i ON t.id=i.id
      WHERE t.taxonomy_id=:id AND i.culture=:culture
      ORDER BY t.id";

    $params = array(':id' => $this->taxonomyId, ':culture' => $culture);

    $terms = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_OBJ));

    foreach ($terms as $term)
    {
      if (!isset($names[$term->name]))
      {
        $names[$term->name] = array();
      }

      array_push($names[$term->name], $term->id);
    }
  }

  protected function normalizeTaxonomy($names, &$affectedObjects)
  {
    foreach ($names as $name => $usage)
    {
      if (count($usage) > 1)
      {
        $this->normalizeTaxonomyTerm($name, $usage, $affectedObjects);
      }
    }
  }

  protected function normalizeTaxonomyTerm($name, $usage, &$affectedObjects)
  {
    $selected_id = array_shift($usage);

    $this->log("Normalizing terms with name '". $name ."'...");

    // Cycle through usage and change to point to selected term
    foreach ($usage as $id)
    {
      $sql = "select object_id from object_term_relation where term_id=?";
      $statement = QubitFlatfileImport::sqlQuery($sql, array($id));
      while ($object = $statement->fetch(PDO::FETCH_OBJ))
      {
        $affectedObjects[] = $object->object_id;
      }

      $this->log("Changing object term relations from term ". $id ." to ". $selected_id .".");

      $sql = "UPDATE object_term_relation SET term_id=:newId WHERE term_id=:oldId";
      $params = array(':newId' => $selected_id, ':oldId' => $id);
      QubitPdo::modify($sql, $params);

      if ($this->taxonomyId == QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID)
      {
        $this->log("Changing level of descriptions from term ". $id ." to ". $selected_id .".");

        $sql = "UPDATE information_object SET level_of_description_id=:newId WHERE level_of_description_id=:oldId";
        QubitPdo::modify($sql, $params);
      }

      $this->log("Deleting term ID ". $id .".");

      // Delete taxonomy term
      $term = QubitTerm::getById($id);
      $term->delete();
    }
  }

  protected function reindexAffectedObjects($affectedObjects)
  {
    $search = QubitSearch::getInstance();
    foreach ($affectedObjects as $id) 
    {
      $o = QubitInformationObject::getById($id);
      $search->update($o);
    }
  }
}
