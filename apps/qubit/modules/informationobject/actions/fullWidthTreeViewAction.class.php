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
 * Action Handler for FullWidth TreeView
 *
 * @package AccesstoMemory
 * @subpackage model
 * @author Andy Koch <koch.andy@gmail.com>
 */
class InformationObjectFullWidthTreeViewAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Get show identifier setting to prepare the reference code if necessary
    $this->showIdentifier = sfConfig::get('app_treeview_show_identifier', 'no');
    $baseReferenceCode = '';

    // On first load, retrieve the ancestors of the selected resource, the resource
    // and its children, otherwise get only the resource's children
    if (filter_var($request->getParameter('firstLoad', false), FILTER_VALIDATE_BOOLEAN)
      && null !== $collectionRoot = $this->resource->getCollectionRoot())
    {
      if ($this->showIdentifier === 'referenceCode')
      {
        // On first load, get the base reference code from the ORM
        $baseReferenceCode = $collectionRoot->getInheritedReferenceCode();
      }

      $data = $this->getNodeOrChildrenNodes($collectionRoot->id, $baseReferenceCode);
    }
    else
    {
      if ($this->showIdentifier === 'referenceCode')
      {
        // Try to get the resource's reference code from the request
        $baseReferenceCode = $request->getParameter('referenceCode');
        if (empty($baseReferenceCode))
        {
          // Or get it from the ORM
          $baseReferenceCode = $this->resource->getInheritedReferenceCode();
        }
      }

      $data = $this->getNodeOrChildrenNodes($this->resource->id, $baseReferenceCode, $children = true);
    }

    $this->getResponse()->setContentType('application/json');

    return $this->renderText(json_encode($data));
  }

  protected function getNodeOrChildrenNodes($id, $baseReferenceCode, $children = false)
  {
    $i18n = sfContext::getInstance()->i18n;
    $culture = $this->getUser()->getCulture();
    $untitled = $i18n->__('Untitled');

    // Remove drafts for unauthenticated users
    $draftsSql = !$this->getUser()->user ? 'AND status.status_id <> ' . QubitTerm::PUBLICATION_STATUS_DRAFT_ID : '' ;

    // Get current node data (for the collection root on the first load) or children data
    $condition = $children ? 'io.parent_id = :id' : 'io.id = :id' ;

    $sql = "SELECT
      io.id, io.lft, io.rgt,
      io.source_culture,
      io.identifier,
      current_i18n.title AS current_title,
      IFNULL(source_i18n.title, '<i>$untitled</i>') as text,
      io.parent_id AS parent,
      slug.slug,
      IFNULL(lod.name, '') AS lod,
      st_i18n.name AS status,
      status.status_id AS status_id
      FROM
        information_object io
        LEFT JOIN information_object_i18n current_i18n ON io.id = current_i18n.id AND current_i18n.culture = :culture
        LEFT JOIN information_object_i18n source_i18n ON io.id = source_i18n.id AND source_i18n.culture = io.source_culture
        LEFT JOIN term_i18n lod ON io.level_of_description_id = lod.id AND lod.culture = :culture
        LEFT JOIN status ON io.id = status.object_id AND status.type_id = :pubStatus
        LEFT JOIN term_i18n st_i18n ON status.status_id = st_i18n.id AND st_i18n.culture = :culture
        LEFT JOIN slug ON io.id = slug.object_id
      WHERE
        $condition
        $draftsSql
      ORDER BY io.lft;";

    $params = array(
      ':culture' => $culture,
      ':pubStatus' => QubitTerm::STATUS_TYPE_PUBLICATION_ID,
      ':id' => $id
    );

    $results = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));
    $data = array();

    foreach ($results as $result)
    {
      // Overwrite source culture title if the current culture title is populated
      if ($this->getUser()->getCulture() != $result['source_culture'] && !empty($result['current_title']))
      {
        $result['text'] = $result['current_title'];
      }

      // Add identifier based on setting
      if ($this->showIdentifier === 'identifier' && !empty($result['identifier']))
      {
        $result['text'] = "{$result['identifier']} - {$result['text']}";
      }

      // Add reference code based on setting
      if ($this->showIdentifier === 'referenceCode' && !empty($baseReferenceCode))
      {
        // Add reference code to the result so it can be sent in subsequent requests
        $result['referenceCode'] = $baseReferenceCode;

        // Append result identifier to the base reference code if we're not loading the collection root
        if ($result['parent'] != QubitInformationObject::ROOT_ID && !empty($result['identifier']))
        {
          $result['referenceCode'] = $result['referenceCode'] . sfConfig::get('app_separator_character', '-') . $result['identifier'];
        }

        $result['text'] = "{$result['referenceCode']} - {$result['text']}";
      }

      // Add level of description based on setting
      if (sfConfig::get('app_treeview_show_level_of_description', 'yes') === 'yes' && strlen($result['lod']) > 0)
      {
        $result['text'] = "[{$result['lod']}] {$result['text']}";
      }

      // Add dates based on setting
      if (sfConfig::get('app_treeview_show_dates', 'no') === 'yes')
      {
        $sql = "SELECT
          event.start_date, event.end_date,
          current_i18n.date AS display_date,
          source_i18n.date AS source_date
          FROM
            event
            LEFT JOIN event_i18n current_i18n ON event.id = current_i18n.id AND current_i18n.culture = :culture
            LEFT JOIN event_i18n source_i18n ON event.id = source_i18n.id AND source_i18n.culture = event.source_culture
          WHERE
            event.object_id = :id;";

        $params = array(
          ':culture' => $culture,
          ':id' => $result['id']
        );

        $events = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

        // As in the search results from ES, get the first event with any date
        foreach ($events as $event)
        {
          if (empty($event['display_date']))
          {
            $event['display_date'] = $event['source_date'];
          }

          if (empty($event['display_date']) && empty($event['start_date']) && empty($event['end_date']))
          {
            continue;
          }

          $date = Qubit::renderDateStartEnd($event['display_date'], $event['start_date'], $event['end_date']);
          $result['text'] = "{$result['text']}, {$date}";

          break;
        }
      }

      if ($result['status_id'] == QubitTerm::PUBLICATION_STATUS_DRAFT_ID)
      {
        $result['text'] = "({$result['status']}) {$result['text']}";
      }

      // Some special flags on our current selected item
      if ($result['id'] == $this->resource->id)
      {
        $result['state'] = array('opened' => true, 'selected' => true);
        $result['li_attr'] = array('selected_on_load' => true);
      }

      // Set root item's parent to hash symbol for jstree compatibility
      if ($result['parent'] == QubitInformationObject::ROOT_ID)
      {
        $result['icon'] = 'fa fa-archive';
      }

      // Add node link attributes
      $result['a_attr']['title'] = $result['text'];
      $result['a_attr']['href'] = $this->generateUrl('slug', array('slug' => $result['slug']));

      // Set children to true for lazy loading or, if we are loading
      // an ancestor of the resource or the resource, load all children
      if ($result['rgt'] - $result['lft'] > 1)
      {
        $result['children'] = true;

        if ($result['lft'] <= $this->resource->lft && $result['rgt'] >= $this->resource->rgt)
        {
          $refCode = '';
          if (!empty($result['referenceCode']))
          {
            $refCode = $result['referenceCode'];
          }

          $result['children'] = $this->getNodeOrChildrenNodes($result['id'], $refCode, $children = true);
        }
      }

      // Not used currently
      $unset = array('lft', 'rgt', 'parent', 'lod', 'status', 'status_id', 'slug', 'source_culture', 'current_title', 'identifier');

      $data[] = array_diff_key($result, array_flip($unset));
    }

    return $data;
  }
}
