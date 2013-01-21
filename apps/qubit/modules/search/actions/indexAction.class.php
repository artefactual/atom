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

class SearchIndexAction extends DefaultBrowseAction
{
  public static
    $FACETS = array(
      'repository.id',
      'subjects.id',
      'digitalObject.mediaTypeId',
      'places.id',
      'names.id',
      array('type' => 'range', 'name' => 'dates.startDate', 'from' => null, 'to' => null),
      array('type' => 'range', 'name' => 'dates.endDate', 'from' => null, 'to' => null));

  protected function populateFacet($name, $ids)
  {
    switch ($name)
    {
      case 'repository.id':
        $criteria = new Criteria;
        $criteria->add(QubitRepository::ID, array_keys($ids), Criteria::IN);

        foreach (QubitRepository::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getAuthorizedFormOfName(array('cultureFallback' => true));
        }

        break;

      case 'subjects.id':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'digitalObject.mediaTypeId':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'dates.startDate':
        $facets[strtr($name, '.', '_')] = $facet['ranges'][0];  // FIXME: is this the best way?

        break;

      case 'places.id':
        $criteria = new Criteria;
        $criteria->add(QubitTerm::ID, array_keys($ids), Criteria::IN);

        foreach (QubitTerm::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getName(array('cultureFallback' => true));
        }

        break;

      case 'names.id':
        $criteria = new Criteria;
        $criteria->add(QubitActor::ID, array_keys($ids), Criteria::IN);

        foreach (QubitActor::get($criteria) as $item)
        {
          $this->types[$item->id] = $item->getAuthorizedFormOfName(array('cultureFallback' => true));
        }

        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ('print' == $request->getGetParameter('media'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    $queryText = new Elastica_Query_QueryString($request->query);
    $queryText->setDefaultOperator('AND');
    # _all? $queryText->setDefaultField(sprintf('i18n.%s.authorizedFormOfName', $this->context->user->getCulture()));
    $this->queryBool->addMust($queryText);

    // Realm filter
    if (isset($this->request->realm) && is_int($this->request->realm))
    {
      $this->queryBool->addMust(new Elastica_Query_Term(array('repository.id' => $this->request->realm)));
    }

    $this->query->setQuery($this->queryBool);

    QubitAclSearch::filterDrafts($this->query);

    // Sort, From, Limit
    $this->query->setSort(array('_score' => 'desc'));
    $this->query->setLimit($this->request->limit);
    if (!empty($this->request->page))
    {
      $query->setFrom(($this->request->page - 1) * $this->request->limit);
    }

    $resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($this->query);

    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);

    if ('print' != $request->getGetParameter('media'))
    {
      $this->pager->setMaxPerPage($request->limit);
    }
    else
    {
      $this->pager->setMaxPerPage(500);
    }

    $this->populateFacets($resultSet);

    // -------------------------------------------------------------------------

    // Populate level of descriptions
    $this->pager->levelsOfDescription = array();
    foreach (QubitTerm::getLevelsOfDescription() as $lod)
    {
      $this->pager->levelsOfDescription[$lod->id] = $lod->getName(array('cultureFallback' => true, 'culture' => $this->context->user->getCulture()));
    }

    // Populate ancestors
    $ancestorsIds = array();
    foreach ($this->pager->getResults() as $hit)
    {
      $doc = $hit->getData();
      foreach ($doc['ancestors'] as $item)
      {
        if (in_array($item, $ancestorsIds))
        {
          continue;
        }

        $ancestorsIds[] = $item;
      }
    }

    $sql = 'SELECT
        io.id,
        o.slug,
        io.title
      FROM '.QubitInformationObjectI18n::TABLE_NAME.' AS io
      LEFT JOIN '.QubitSlug::TABLE_NAME.' AS o ON (o.object_id = io.id AND io.culture = ?)
      WHERE o.object_id IN ('.implode(',', $ancestorsIds).')';
    $this->pager->ancestors = array();
    foreach (QubitPdo::fetchAll($sql, array($this->context->user->getCulture())) as $ancestor)
    {
      $this->pager->ancestors[$ancestor->id] = array(
        'slug' => $ancestor->slug,
        'title' => $ancestor->title);
    }
  }
}
