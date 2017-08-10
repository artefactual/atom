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
 * Bulk export data about usage of term as CSV
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvExportTermUsageTask extends exportBulkBaseTask
{
  protected $namespace        = 'csv';
  protected $name             = 'export-term-usage';
  protected $briefDescription = 'Export terms associated, with information objects, as CSV file(s)';

  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addCoreArgumentsAndOptions();

    $this->addOptions(array(
      new sfCommandOption('taxonomy-id', null, sfCommandOption::PARAMETER_OPTIONAL, 'ID of taxonomy')
    ));
    $this->addOptions(array(
      new sfCommandOption('taxonomy-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'Name of taxonomy')
    ));
    $this->addOptions(array(
      new sfCommandOption('taxonomy-name-culture', null, sfCommandOption::PARAMETER_OPTIONAL, 'Culture to use for taxonomy name lookup')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if (isset($options['items-until-update']) && !ctype_digit($options['items-until-update']))
    {
      throw new sfException('items-until-update must be a number');
    }

    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $sf_context = sfContext::createInstance($configuration);
    $conn = $this->getDatabaseConnection();

    $this->exportFileReplacePrompt($arguments['path']);
    $itemsExported = $this->exportToCsv($this->determineTaxonomyId($options), $arguments['path'], $options['items-until-update']);

    if ($itemsExported)
    {
      $this->log(sprintf("\nExport complete (%d terms exported).", $itemsExported));
    }
    else
    {
      $this->log("No term usages found to export.");
    }
  }

  private function determineTaxonomyId($options)
  {
    if (ctype_digit($options['taxonomy-id']))
    {
      $criteria = new Criteria;
      $criteria->add(QubitTaxonomy::ID, $options['taxonomy-id']);

      if (null === QubitTaxonomy::getOne($criteria))
      {
        throw new sfException('Invalid taxonomy-id.');
      }

      return $options['taxonomy-id'];
    }
    elseif (isset($options['taxonomy-name']))
    {
      $culture = (isset($options['taxonomy-name-culture'])) ? $options['taxonomy-name-culture'] : 'en';

      $criteria = new Criteria;

      $criteria->add(QubitTaxonomyI18n::NAME, $options['taxonomy-name']);
      $criteria->add(QubitTaxonomyI18n::CULTURE, $culture);

      if (null === $taxonomy = QubitTaxonomyI18n::getOne($criteria))
      {
        throw new sfException('Invalid taxonomy-name and/or taxonomy-name-culture.');
      }

      return $taxonomy->id;
    }

    throw new sfException('Either the taxonomy-id or taxonomy-name must be used to specifiy a taxonomy.');
  }

  private function exportFileReplacePrompt($exportPath)
  {
    if (file_exists($exportPath))
    {
      if (strtolower(readline('The export file already exists. Do you want to replace it? [y/n*] ')) != 'y')
      {
        throw new sfException('Export file already exists: aborting.');
      }

      unlink(realpath($exportPath));
    }
  }

  private function exportToCsv($taxonomyId, $exportPath, $rowsUntilUpdate)
  {
    $itemsExported = 0;

    /*
    Idea for future:

    1. LEFT JOIN could include unused terms so we could count those too
    2. Add --mode=used|both|unused-only ("used" as default) to flip between LEFT and INNER join (and maybe add criteria for specific modes)

    Would also be good to add logic to QubitFlatfileExport so cultureFallback can be done by the class rather than manually
    */
    $format = 'SELECT DISTINCT t.id, COUNT(i.id) AS use_count FROM %s t INNER JOIN %s r ON r.term_id=t.id INNER JOIN %s i ON r.object_id=i.id WHERE t.taxonomy_id=? GROUP BY (t.id) ORDER BY t.id';
    $sql = sprintf($format, QubitTerm::TABLE_NAME, QubitObjectTermRelation::TABLE_NAME, QubitInformationObject::TABLE_NAME);

    $result = QubitPdo::prepareAndExecute($sql, array($taxonomyId));

    if ($result->rowCount())
    {
      // Instantiate CSV writer using "usage" column ordering
      $writer = new QubitFlatfileExport($exportPath, 'usage');
      $writer->loadResourceSpecificConfiguration('QubitTerm');

      while ($row = $result->fetch(PDO::FETCH_OBJ))
      {
        $resource = QubitTerm::getById($row->id);
        $writer->setColumn('name', $resource->getName(array('cultureFallback' => true)));
        $writer->setColumn('use_count', $row->use_count);
        $writer->exportResource($resource);

        $this->indicateProgress($rowsUntilUpdate);

        $itemsExported++;
      }
    }

    return $itemsExported;
  }
}
