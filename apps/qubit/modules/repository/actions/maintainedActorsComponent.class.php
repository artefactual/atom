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
 * Repository maintained actors component
 *
 * @package AccesstoMemory
 * @subpackage repository
 */
class RepositoryMaintainedActorsComponent extends sfComponent
{
  public function execute($request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

    $page = 1;
    $limit = sfConfig::get('app_hits_per_page', 10);

    $resultSet = RepositoryMaintainedActorsAction::getActors($this->resource->id, $page, $limit);
    if ($resultSet->getTotalHits() == 0)
    {
      return sfView::NONE;
    }

    $pager = new QubitSearchPager($resultSet);
    $pager->setPage($page);
    $pager->setMaxPerPage($limit);
    $pager->init();

    $this->list = array(
      'label' => $this->context->i18n->__('Maintainer of'),
      'pager' => $pager,
      'dataUrl' => url_for(array('module' => 'repository', 'action' => 'maintainedActors', 'repositoryId' => $this->resource->id)),
      'moreUrl' => url_for(array('module' => 'actor', 'action' => 'browse', 'maintainingRepository' => $this->resource->id)));
  }
}
