<?php
/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class SearchAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    // Store user query string
    $this->queryString = strtr($request->query, array('*' => '', '?' => ''));

    // If the query is empty, don't query
    if ('' == preg_replace('/[\s\t\r\n]*/', '', $this->queryString))
    {
      return sfView::NONE;
    }
    // Current culture
    $this->culture = $this->context->user->getCulture();

    // Build ES multi query
    $this->queries = array();
    $this->buildQuery('atom', 'QubitInformationObject', 'i18n.%s.title', 3, array('_score' => 'desc'));
    $this->buildQuery('atom', 'QubitRepository', 'i18n.%s.authorizedFormOfName', 3, array('_score' => 'desc'));
    $this->buildQuery('atom', 'QubitActor', 'i18n.%s.authorizedFormOfName', 3, array('_score' => 'desc'));
    $this->buildQuery('atom', 'QubitTerm', 'i18n.%s.name', 3, array('_score' => 'desc'));

    // Get a list of result sets
    $resultSets = $this->sendMultiQuery();

    // Direct access to result set objects
    $this->descriptions = $resultSets[0];
    $this->repositories = $resultSets[1];
    $this->actors = $resultSets[2];
    $this->subjects = $resultSets[3];
  }

  protected function buildQuery($index, $type, $field, $size, $sort)
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
      'size' => $size,
      'sort' => $sort);
  }

  /**
   * Elastica does not support _msearch yet. This method sends the query using
   * Elastica_Client::request(). Multiple Elastica_Response objects are build
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

    $response = QubitSearch::getInstance()->client->request('_msearch', Elastica_Request::GET, $rawQuery);
    $responseData = $response->getData();

    $resultSets = array();

    if (isset($responseData['responses']) && is_array($responseData['responses']))
    {
      foreach ($responseData['responses'] as $key => $responseData)
      {
        $response = new Elastica_Response(json_encode($responseData));

        $resultSets[] = new Elastica_ResultSet($response);
      }
    }

    return $resultSets;
  }
}
