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
 * arElasticSearchPlugin main class
 *
 * @package     AccesstoMemory
 * @subpackage  search
 */
class arDrmcBootstrapTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'If passed, progress is displayed for each object indexed')));

    $this->namespace = 'drmc';
    $this->name = 'bootstrap';

    $this->briefDescription = 'Bootstrap DRMC-MA database';
    $this->detailedDescription = <<<EOF
The [drmc:bootstrap|INFO] task adds the necessary initial data to your database
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);

    new sfDatabaseManager($this->configuration);

    $this->addLevelsOfDescriptions();
  }

  protected function addLevelsOfDescriptions()
  {
    // Remove AtoM's defaults
    foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID, array('level' => 'top')) as $item)
    {
      $target = array('Fonds', 'Subfonds', 'Collection', 'Series', 'Subseries', 'File', 'Item');
      $name = $item->getName(array('culture' => 'en'));
      if (in_array($name, $target))
      {
        $item->delete();
      }
    }

    // Levels of description specific for MoMA DRMC-MA
    $levels = array(
      array('name' => 'Artwork record'),
      array('name' => 'Description'),
      array('name' => 'Physical component', 'children' => array(
        array('name' => 'Artist supplied master'),
        array('name' => 'Artist verified proof'),
        array('name' => 'Archival master'),
        array('name' => 'Exhibition format'),
        array('name' => 'Equipment'))),
      array('name' => 'Digital component'),
      array('name' => 'Supporting technology record'));

    // Find a specific level of description by its name (in English)
    $find = function($name)
    {
      $criteria = new Criteria;
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $name);
      $criteria->add(QubitTermI18n::CULTURE, 'en');

      return null !== QubitTerm::getOne($criteria);
    };

    // Parse $levels recursively and add the levels to the taxonomy
    $add = function($levels, $parentId = false) use (&$find, &$add)
    {
      if (false === $parentId)
      {
        $parentId = QubitTerm::ROOT_ID;
      }

      foreach ($levels as $level)
      {
        // Don't duplicate
        if (true === $find($level['name']))
        {
          continue;
        }

        $term = new QubitTerm;
        $term->name  = $level['name'];
        $term->taxonomyId = QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID;
        // $term->code
        $term->parentId = $parentId;
        $term->culture = 'en';
        $term->save();

        if (isset($level['children']))
        {
          $add($level['children'], $term->id);
        }
      }
    };

    $add($levels);
  }
}
