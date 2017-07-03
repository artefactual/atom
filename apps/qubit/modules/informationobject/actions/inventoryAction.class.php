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

class InformationObjectInventoryAction extends DefaultBrowseAction
{
  private static $levels;

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Set title header
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Qubit'));
    $title = render_title($this->resource, false);
    $this->response->setTitle("$title - Inventory list - {$this->response->getTitle()}");

    if (empty($request->limit))
    {
      $request->limit = sfConfig::get('app_hits_per_page');
    }

    $resultSet = self::getResults($this->resource, $request->limit, $request->page, $request->sort);

    // Page results
    $this->pager = new QubitSearchPager($resultSet);
    $this->pager->setPage($request->page ? $request->page : 1);
    $this->pager->setMaxPerPage($request->limit);
    $this->pager->init();
  }

  private static function getLevels()
  {
    if (self::$levels !== null)
    {
      return self::$levels;
    }

    $setting = QubitSetting::getByName('inventory_levels');
    if (null === $setting || false === $value = unserialize($setting->getValue()))
    {
      return;
    }

    if (!is_array($value) || 0 === count($value))
    {
      return;
    }

    self::$levels = $value;

    return $value;
  }

  private static function getResults($resource, $limit = 10, $page = 1, $sort = null)
  {
    $query = new \Elastica\Query;
    $query->setSize($limit);
    if (!empty($page))
    {
      $query->setFrom(($page - 1) * $limit);
    }

    $queryBool = new \Elastica\Query\BoolQuery;

    $q1 = new \Elastica\Query\Term;
    $q1->setTerm('ancestors', $resource->id);
    $queryBool->addMust($q1);
    $q2 = new \Elastica\Query\Terms;
    $q2->setTerms('levelOfDescriptionId', self::getLevels());
    $queryBool->addMust($q2);

    $i18n = sprintf('i18n.%s.', sfContext::getInstance()->getUser()->getCulture());
    switch ($sort)
    {
      case 'identifierDown':
        $query->setSort(array('identifier' => 'desc'));

        break;

      case 'titleUp':
        $query->setSort(array($i18n.'title.untouched' => 'asc'));

        break;

      case 'titleDown':
        $query->setSort(array($i18n.'title.untouched' => 'desc'));

        break;

      case 'levelUp':
        $query->setSort(array('levelOfDescriptionId' => 'asc'));

        break;

      case 'levelDown':
        $query->setSort(array('levelOfDescriptionId' => 'desc'));

        break;

      case 'dateUp':
        $query->setSort(array(
          'dates.startDate' => 'asc',
          'dates.endDate' => 'asc'));

        break;

      case 'dateDown':
        $query->setSort(array(
          'dates.startDate' => 'desc',
          'dates.endDate' => 'desc'));

        break;

      // Avoid sorting when we are just counting records
      case null:
        break;

      case 'identifierUp':
      default:
        $query->setSort(array('identifier' => 'asc'));
    }

    QubitAclSearch::filterDrafts($queryBool);
    $query->setQuery($queryBool);

    return QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);
  }

  public static function showInventory($resource)
  {
    if (count(self::getLevels()) == 0)
    {
      return false;
    }

    $resultSet = self::getResults($resource);

    return $resultSet->getTotalHits() > 0;
  }
}
