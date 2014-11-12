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
 * Show paginated list of hits to access log.
 *
 * @package    AccesstoMemory
 * @subpackage status
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class RepositoryPopularAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->context->user->isAdministrator())
    {
      // 403 - Forbidden
      $this->getResponse()->setStatusCode(403);

      return sfView::HEADER_ONLY;
    }

    // Take note of resource and 404 if not valid resource
    $this->resource = $this->getRoute()->resource;
    if (!isset($this->resource))
    {
      $this->forward404();
    }

    // Expose date parameters to template
    $this->startDate = $request->start_date;
    $this->endDate = $request->end_date;

    // Assemble SQL queries
    list ($reportCountSql, $reportCountSqlParams) = $this->reportCountSqlAndParams($request);
    list ($reportSql, $reportSqlParams) = $this->reportSql($request);

    // Paginate results
    $this->pager = new QubitPdoPager($reportSql, $reportSqlParams, $reportCountSql, $reportCountSqlParams);
    $this->pager->setMaxPerPage(10);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->objects = $this->pager->getResults();

    // Look up resource and parent resource details
    $this->resources = array();
    $this->parents   = array();

    foreach($this->objects as $object)
    {
      $resource = QubitInformationObject::getById($object->object_id);
      $this->resources[$object->object_id] = $resource;

      if ($object->bot_parent_id != QubitInformationObject::ROOT_ID)
      {
        foreach ($resource->ancestors->andSelf()->orderBy('rgt') as $item)
        {
          // Stop iteration before the root object is reached
          if (QubitInformationObject::ROOT_ID == $item->parentId)
          {
            $this->parents[($object->object_id)] = $item;
            break;
          }
        }
      }
    }

    // Calculate starting rank on this page
    $this->rank = (($this->pager->getPage() - 1) * $this->pager->getMaxPerPage()) + 1;
  }

  protected function baseSqlClause()
  {
    // Get information objects for access log, self-joining to get repository ID
    // from top-level description
    return 'FROM information_object bot
       JOIN information_object top
         ON bot.lft >= top.lft AND bot.rgt <= top.rgt
       JOIN access_log on bot.id = access_log.object_id
       WHERE top.repository_id = '.  $this->resource->id .' ';
  }

  protected function dateFilterSqlClause($request, &$params)
  {
    $dateFilterClause = '';

    if (isset($request->start_date) && !empty($request->start_date))
    {
      $dateFilterClause .= "AND access_log.access_date >= :start ";
      $params[':start'] = $request->start_date;
    }

    if (isset($request->end_date) && !empty($request->end_date))
    {
      $dateFilterClause .= "AND access_log.access_date <= :end ";
      $params[':end'] = $request->end_date;
    }

    return $dateFilterClause;
  }

  protected function reportCountSqlAndParams($request)
  {
    $params = array();

    // SQL to get hit count
    $reportCountSql = 'SELECT COUNT(DISTINCT access_log.object_id) AS hits '. $this->baseSqlClause();

    // Filter by date
    $reportCountSql .= $this->dateFilterSqlClause($request, $params);

    return array($reportCountSql, $params);
  }

  protected function reportSql($request)
  {
    $params = array();

    // SQL to get results
    $reportSql = 'SELECT access_log.object_id, bot.parent_id AS bot_parent_id, COUNT(*) AS visits '. $this->baseSqlClause();

    // Filter by date
    $reportSql .= $this->dateFilterSqlClause($request, $params);

    // Add grouping/ordering
    $sql = $reportSql . 'GROUP BY access_log.object_id ORDER BY visits DESC';

    return array($sql, $params);
  }
}
