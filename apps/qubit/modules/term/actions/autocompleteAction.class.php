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

class TermAutocompleteAction extends sfAction
{
  public function execute($request)
  {
    if (!isset($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    // For term module show only preferred term
    $params = $this->context->routing->parse(Qubit::pathInfo($request->getReferer()));
    if ('term' == $params['module'])
    {
      $criteria = new Criteria;

      // Exclude the calling object and it's descendants from the list (prevent
      // circular inheritance)
      if (isset($params['_sf_route']->resource->id))
      {
        // TODO Self join would be ideal
        $criteria->add($criteria->getNewCriterion(QubitTerm::LFT, $params['_sf_route']->resource->lft, Criteria::LESS_THAN)
          ->addOr($criteria->getNewCriterion(QubitTerm::RGT, $params['_sf_route']->resource->rgt, Criteria::GREATER_THAN)));
      }

      // Filter by parent if it's set
      if (isset($request->parent))
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($request->parent));
        $criteria->add(QubitTerm::PARENT_ID, $params['_sf_route']->resource->id);
      }

      $params = $this->context->routing->parse(Qubit::pathInfo($request->taxonomy));
      $criteria->add(QubitTerm::TAXONOMY_ID, $params['_sf_route']->resource->id);

      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::CULTURE, $this->context->user->getCulture());

      // Narrow results by query
      if (isset($request->query))
      {
        $criteria->add(QubitTermI18n::NAME, "$request->query%", Criteria::LIKE);
      }

      // Sort by name
      $criteria->addAscendingOrderByColumn(QubitTermI18n::NAME);

      $criteria->setLimit($request->limit);

      $this->terms = QubitTerm::get($criteria);
    }

    // If NOT calling from term page show preferred and alternative terms
    else
    {
      $where = '';
      if (isset($request->query))
      {
       $where = ' WHERE name LIKE :p3';
      }

      $s1 = 'SELECT qt.id, null, qti.name
        FROM '.QubitTerm::TABLE_NAME.' qt
          LEFT JOIN (SELECT id, name, culture FROM '.QubitTermI18n::TABLE_NAME.$where.') qti
            ON qt.id = qti.id
        WHERE taxonomy_id = :p1
          AND qti.culture = :p2';

      if (isset($request->parent))
      {
       $s1 .= ' AND parent_id = :p5';
      }

      $s2 = 'SELECT qt.id, qon.id as altId, qoni.name
        FROM '.QubitOtherName::TABLE_NAME.' qon
          INNER JOIN (SELECT id, culture, name FROM '.QubitOtherNameI18n::TABLE_NAME.$where.') qoni
            ON qon.id = qoni.id
          INNER JOIN '.QubitTerm::TABLE_NAME.' qt
            ON qon.object_id = qt.id
        WHERE qt.taxonomy_id = :p1
          AND qoni.culture = :p2';

      // Narrow results by query
      if (isset($request->query))
      {
       $s2 .= ' AND qoni.name LIKE :p3';
      }

      // Narrow results by parent
      if (isset($request->parent))
      {
        $s2 .= ' AND qt.parent_id = :p5';
      }

      $connection = Propel::getConnection();
      $statement = $connection->prepare("($s1) UNION ALL ($s2) ORDER BY name LIMIT :p4");
      $params = $this->context->routing->parse(Qubit::pathInfo($request->taxonomy));

      $statement->bindValue(':p1', $params['_sf_route']->resource->id);
      $statement->bindValue(':p2', $this->context->user->getCulture());

      if (isset($request->query))
      {
        $statement->bindValue(':p3', "$request->query%");
      }

      if (isset($request->parent))
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($request->parent));
        $statement->bindValue(':p5', $params['_sf_route']->resource->id);
      }

      $statement->bindValue(':p4', (int) $request->limit, PDO::PARAM_INT);

      $statement->execute();

      $this->terms = array();
      $rows = $statement->fetchAll();
      foreach ($rows as $row)
      {
        if (isset($row[1]))
        {
          // Alternative term
          $this->terms[] = array(QubitTerm::getById($row[0]), $row[2]);
        }
        else
        {
          // Preferred term
          $this->terms[] = QubitTerm::getById($row[0]);
        }
      }
    }
  }
}
