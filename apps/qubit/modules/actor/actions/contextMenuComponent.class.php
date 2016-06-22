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
 * Actor contextMenu component
 *
 * @package AccesstoMemory
 * @subpackage actor
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 */
class ActorContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

    $page = 1;
    $limit = sfConfig::get('app_hits_per_page', 10);

    // Store related information objects in lists with pagination, lists for
    // all event types and for name access point relations (subject of)
    $this->lists = array();

    // Subject of
    $resultSet = ActorRelatedInformationObjectsAction::getRelatedInformationObjects($this->resource->id, $page, $limit);

    if ($resultSet->getTotalHits() > 0)
    {
      $pager = new QubitSearchPager($resultSet);
      $pager->setPage($page);
      $pager->setMaxPerPage($limit);
      $pager->init();

      $this->lists[] = array(
        'label' => $this->context->i18n->__('Subject'),
        'pager' => $pager,
        'dataUrl' => url_for(array('module' => 'actor', 'action' => 'relatedInformationObjects', 'actorId' => $this->resource->id)),
        'moreUrl' => url_for(array('module' => 'informationobject', 'action' => 'browse', 'topLod' => 0, 'names' => $this->resource->id)));
    }

    // All event types
    foreach (QubitTerm::getEventTypes() as $eventType)
    {
      $resultSet = ActorRelatedInformationObjectsAction::getRelatedInformationObjects($this->resource->id, $page, $limit, $eventType->id);

      if ($resultSet->getTotalHits() > 0)
      {
        $pager = new QubitSearchPager($resultSet);
        $pager->setPage($page);
        $pager->setMaxPerPage($limit);
        $pager->init();

        $this->lists[] = array(
          'label' => $eventType->getRole(),
          'pager' => $pager,
          'dataUrl' => url_for(array('module' => 'actor', 'action' => 'relatedInformationObjects', 'actorId' => $this->resource->id, 'eventTypeId' => $eventType->id)),
          'moreUrl' => url_for(array('module' => 'informationobject', 'action' => 'browse', 'topLod' => 0, 'actorId' => $this->resource->id, 'eventTypeId' => $eventType->id)));
      }
    }
  }
}
