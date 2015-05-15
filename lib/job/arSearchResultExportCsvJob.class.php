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
 * A worker to, given the HTTP GET parameters sent to advanced search,
 * replicate the search and export the resulting decriptions to CSV.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arSearchResultExportCsvJob extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('params');  // search params

  protected $searchParams;      // key/value array of search terms
  protected $archivalStandard;  // examples: "rad", "isad"
  protected $search;            // arElasticSearchPluginQuery instance

  public function runJob($parameters)
  {
    $this->searchParams = $parameters['params'];
    $this->archivalStandard = QubitSetting::getByNameAndScope('informationobject', 'default_template');
    $this->search = new arElasticSearchPluginQuery();

    $this->addCriteriaBasedOnSearchParameters();

    $exportPath = tempnam(sys_get_temp_dir(), 'search_export_') .'.csv';

    print 'Exporting to '. $exportPath ."...\n";

    $itemsExported = $this->exportResults($exportPath);

    print 'Exported '. $itemsExported ." descriptions.\n";

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }

  /**
   * Add criteria to query based on parameters.
   *
   * @return void
   */
  protected function addCriteriaBasedOnSearchParameters()
  {
    // Add criteria for main search fields
    if (null !== $criterias = $this->parseQuery())
    {
      $this->search->queryBool->addMust($criterias);
    }

    // Add criteria fo secondary search fields
    foreach (SearchAdvancedAction::$NAMES as $name)
    {
      if (
        !empty($this->searchParams[$name])
        && (null !== $criterias = SearchAdvancedAction::fieldCriteria($name, $this->searchParams[$name]))
      )
      {
        $this->search->queryBool->addMust($criterias);
      }
    }

    // Set query if criteria were added
    if (count($this->search->queryBool->getParams()))
    {
      $this->search->query->setQuery($this->search->queryBool);
    }
  }

  /**
   * Translate array of search parameters to query criteria.
   *
   * Modified version of parseQuery method in the SearchAdvancedAction class
   *
   * Each set of parameters is numbered, starting at zero, and includes three
   * properties: query text (prefixed by "sq"), operation (prefixed by "so": "and" or
   * "or"), and fields (prefixed by "sf") to return (defaulting to "_all").
   *
   * For example:
   *
   *   $this->searchParams = array(
   *     'so0' => 'and',
   *     'sq0' => 'cats',
   *     'sf0' => ''
   *   );
   *
   * @return object  \Elastica\Query\Bool instance
   */
  protected function parseQuery()
  {
    $queryBool = new \Elastica\Query\Bool();

    $count = -1;

    while (null !== $query = $this->searchParams['sq'.++$count])
    {
      if (empty($query)) continue;

      $field = $this->searchParams['sf'.$count];
      if (empty($field))
      {
        $field = '_all';
      }

      $operator = $this->searchParams['so'.$count];
      if (empty($operator))
      {
        $operator = 'or';
      }

      $queryField = SearchAdvancedAction::queryField($field, $query, $this->archivalStandard);
      SearchAdvancedAction::addToQueryBool($queryBool, $operator, $queryField);
    }

    if (0 == count($queryBool->getParams()))
    {
      return;
    }

    return $queryBool;
  }

  /**
   * Export search results as CSV
   *
   * @param string  Path of file to write CSV data to
   *
   * @return int  Number of descriptions exported
   */
  protected function exportResults($path)
  {
    $itemsExported = 0;

    $writer = new csvInformationObjectExport(
      $path,
      $this->archivalStandard
    );

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->search->query);

    foreach ($resultSet as $hit)
    {
      $resource = QubitInformationObject::getById($hit->getId());

      $writer->exportResource($resource);

      print '.';

      $itemsExported++;
    }

    return $itemsExported;
  }
}
