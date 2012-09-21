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

/**
 * Browse list for digital objects
 *
 * @package    qubit
 * @subpackage digitalobject
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectBrowseAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // Force limit temporary
    $request->limit = 250;

    $queryBool = new Elastica_Query_Bool();
    $queryBool->addShould(new Elastica_Query_MatchAll());
    $queryBool->addMust(new Elastica_Query_Term(array('hasDigitalObject' => true)));

    $query = new Elastica_Query();
    $query->setLimit($request->limit);
    $query->setQuery($queryBool);

    $facet = new Elastica_Facet_Terms('mediaTypeId');
    $facet->setField('mediaTypeId');
    $facet->setSize(50);
    $query->addFacet($facet);

    if (!empty($request->page))
    {
      $query->setFrom(($request->page - 1) * $request->limit);
    }

    try
    {
      $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
    }
    catch (Exception $e)
    {
      $this->error = $e->getMessage();

      return;
    }

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);

    if ($this->pager->hasResults())
    {
      $facets = array();

      foreach ($this->pager->getFacets() as $name => $facet)
      {
        if (isset($facet['terms']))
        {
          $ids = array();
          foreach ($facet['terms'] as $item)
          {
            $ids[$item['term']] = $item['count'];
          }
        }

        switch ($name)
        {
          case 'types':
            $criteria = new Criteria;
            $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);
            $types = QubitTerm::get($criteria);

            foreach ($types as $item)
            {
              $typeNames[$item->id] = $item->name;
            }

            foreach ($facet['terms'] as $item)
            {
              $facets[strtr($name, '.', '_')]['terms'][$item['term']] = array(
                'count' => $item['count'],
                'term' => $typeNames[$item['term']]);
            }

            break;

          case 'contact.i18n.region':
            foreach ($facet['terms'] as $item)
            {
              $facets[strtr($name, '.', '_')]['terms'][$item['term']] = array(
                'count' => $item['count'],
                'term' => $item['term']);
            }

            break;
        }
      }

      $this->pager->facets = $facets;
    }
  }
}
