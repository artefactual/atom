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

    $this->log("Normalizing for '". $options['culture'] ."' culture...");

    // look up taxonomy ID using name
    $taxonomyId = $this->getTaxonomyIdByName($arguments['taxonomy-name'], $options['culture']);
    if (!$taxonomyId)
    {
      throw new sfException("A taxonomy named '". $arguments['taxonomy-name'] ."' not found for culture '". $options['culture'] ."'.");
    }

    // determine taxonomy term usage then normalize
    $names = array();
    $this->populateTaxonomyNameUsage($names, $taxonomyId, $options['culture']);
    $this->normalizeTaxonomy($names);
  }

  protected function getTaxonomyIdByName($name, $culture)
  {
    $sql = "SELECT id FROM taxonomy_i18n \r
      WHERE culture=? \r
      AND name=?";

    $statement = QubitFlatfileImport::sqlQuery($sql, array($culture, $name));

    if($object = $statement->fetch(PDO::FETCH_OBJ))
    {
      return $object->id;
    } else {
      return false;
    }
  }

  protected function populateTaxonomyNameUsage(&$names, $taxonomyId, $culture)
  {
    $sql = "SELECT t.id, i.name FROM term t \r
      INNER JOIN term_i18n i ON t.id=i.id \r
      WHERE t.taxonomy_id=? AND i.culture=? \r
      ORDER BY t.id";

    $statement = QubitFlatfileImport::sqlQuery($sql, array($taxonomyId, $culture));

    while($object = $statement->fetch(PDO::FETCH_OBJ))
    {
      if (!isset($names[$object->name]))
      {
        $names[$object->name] = array();
      }
      array_push($names[$object->name], $object->id);
    }
  }

  protected function normalizeTaxonomy($names)
  {
    foreach($names as $name => $usage)
    {
      if (count($usage) > 1)
      {
        $this->normalizeTaxonomyTerm($name, $usage);
      }
    }
  }

  protected function normalizeTaxonomyTerm($name, $usage)
  {
    $selected_id = array_shift($usage);

    $this->log("Normalizing terms with name '". $name ."'...");

    // cycle through usage and change to point to selected term
    if (count($usage))
    {
      // delete now unused terms
      foreach($usage as $id)
      {
        $this->log("Changing object term relations to term ". $id ." to ". $selected_id .".");

        $sql = "UPDATE object_term_relation \r
          SET term_id=? WHERE term_id=?";

        $statement = QubitFlatfileImport::sqlQuery($sql, array($selected_id, $id));

        $this->log("Deleting term ID ". $id .".");

        // delete taxonomy term
        $term = QubitTerm::getById($id);
        $term->delete();
      }
    } else {
      print "Already normalized.\n";
    }
  }
}
