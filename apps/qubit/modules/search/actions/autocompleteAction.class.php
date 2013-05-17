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
 * Use _msearch to query ES multiple times at once, using query_string.
 *
 * @package AccesstoMemory
 * @subpackage search
 */
class SearchAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    // Store user query string, erase wildcards
    $this->queryString = strtr($request->query, array('*' => '', '?' => ''));

    // If the query is empty, don't query
    if ('' == preg_replace('/[\s\t\r\n]*/', '', $this->queryString))
    {
      $this->forward404();
    }

    // Add wildcard
    $this->queryString .= '*';

    // Current culture
    $this->culture = $this->context->user->getCulture();

    // Build ES multi query
    $this->queries = array();

    $index = QubitSearch::getInstance()->index->getName();

    $this->buildQuery(
      $index,
      'QubitInformationObject',
      'i18n.%s.title',
      array('slug', 'i18n', 'levelOfDescriptionId'),
      array('_score' => 'desc'));

    $this->buildQuery(
      $index,
      'QubitRepository',
      'i18n.%s.authorizedFormOfName',
      array('slug', 'i18n'),
      array('_score' => 'desc'));

    $this->buildQuery(
      $index,
      'QubitActor',
      'i18n.%s.authorizedFormOfName',
      array('slug', 'i18n'),
      array('_score' => 'desc'));

    $this->buildQuery(
      $index,
      'QubitTerm',
      'i18n.%s.name',
      array('slug', 'i18n'),
      array('_score' => 'desc'));

    // Get a list of result sets
    $resultSets = $this->sendMultiQuery();

    // Direct access to result set objects
    $this->descriptions = $resultSets[0];
    $this->repositories = $resultSets[1];
    $this->actors = $resultSets[2];
    $this->subjects = $resultSets[3];

    // Return a 404 response if there are no results
    if (0 == $this->descriptions->getTotalHits() + $this->repositories->getTotalHits() + $this->actors->getTotalHits() + $this->subjects->getTotalHits())
    {
      $this->forward404();
    }

    // Preload levels of descriptions
    if (0 < $this->descriptions->getTotalHits())
    {
      $sql = '
        SELECT
          t.id,
          ti18n.name
        FROM
          '.QubitTerm::TABLE_NAME.' AS t
        LEFT JOIN '.QubitTermI18n::TABLE_NAME.' AS ti18n ON (t.id = ti18n.id AND ti18n.culture = ?)
        WHERE
          t.taxonomy_id = ?';

      $this->levelsOfDescription = array();
      foreach (QubitPdo::fetchAll($sql, array($this->context->user->getCulture(), QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID)) as $item)
      {
        $this->levelsOfDescription[$item->id] = $item->name;
      }
    }
  }

  protected function buildQuery($index, $type, $field, $fields, $sort)
  {
    $fieldName = sprintf($field, $this->culture);

    // Header
    $this->queries[] = array(
      'index' => $index,
      'type' => $type);

    $this->queries[] = array(
      'query' => array(
        'query_string' => array(
          'default_field' => $fieldName,
          'default_operator' => 'AND',
          'query' => $this->queryString)),
      'fields' => $fields,
      'size' => 3,
      'sort' => $sort);
  }

  /**
   * Elastica does not support _msearch yet. This method sends the query using
   * \Elastica\Client::request(). Multiple \Elastica\Response objects are build
   * but json_encode has to be called. I wonder if it could be avoided some way.
   *
   * @return array
   */
  protected function sendMultiQuery()
  {
    $rawQuery = '';
    foreach ($this->queries as $query)
    {
      $rawQuery .= (is_array($query) ? json_encode($query) : $query) . PHP_EOL;
    }

    $response = QubitSearch::getInstance()->client->request('_msearch', \Elastica\Request::GET, $rawQuery);
    $responseData = $response->getData();

    $resultSets = array();

    if (isset($responseData['responses']) && is_array($responseData['responses']))
    {
      foreach ($responseData['responses'] as $key => $responseData)
      {
        $response = new \Elastica\Response(json_encode($responseData));

        $resultSets[] = new \Elastica\ResultSet($response);
      }
    }

    return $resultSets;
  }
}
