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
class DefaultFullTreeViewAction extends sfAction
{
  public function execute($request)
  {
    ProjectConfiguration::getActive()->loadHelpers('Qubit');

    // Get show identifier setting to prepare the reference code if necessary
    $this->showIdentifier = sfConfig::get('app_treeview_show_identifier', 'no');

    // Set response to JSON
    $this->getResponse()->setContentType('application/json');
  }

  protected function getNodeOrChildrenNodes($id, $baseReferenceCode, $children = false, $options = array())
  {
    $i18n = sfContext::getInstance()->i18n;
    $culture = $this->getUser()->getCulture();

    if (sfConfig::get('app_markdown_enabled', true))
    {
      $untitled = '_'.$i18n->__('Untitled').'_';
    }
    else
    {
      $untitled = '<em>'.$i18n->__('Untitled').'</em>';
    }

    // Remove drafts for unauthenticated users
    $draftsSql = !$this->getUser()->user ? 'AND status.status_id <> ' . QubitTerm::PUBLICATION_STATUS_DRAFT_ID : '' ;

    // Get current node data (for the collection root on the first load) or children data
    $condition = $children ? 'io.parent_id = :id' : 'io.id = :id' ;

    // Determine ordering
    $orderColumn = (isset($options['orderColumn'])) ? $options['orderColumn'] : 'io.lft';

    // If we're currently fetching children and paging options are set,
    // use paging options to assemble LIMIT clause
    $limitClause = "";
    $skip = (isset($options['skip'])) ? $options['skip'] : null;
    $limit = (isset($options['limit'])) ? $options['limit'] : null;

    if ($children && (ctype_digit($skip) || ctype_digit($limit)))
    {
      $limitClause = "LIMIT ";
      $limitClause .= (ctype_digit($skip)) ? $skip : "0";
      $limitClause .= (ctype_digit($limit)) ? ", ". $limit : "";
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS
      io.id, io.lft, io.rgt,
      io.source_culture,
      io.identifier,
      current_i18n.title AS current_title,
      IFNULL(source_i18n.title, '$untitled') as text,
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
      ORDER BY $orderColumn
      $limitClause;";

    $params = array(
      ':culture' => $culture,
      ':pubStatus' => QubitTerm::STATUS_TYPE_PUBLICATION_ID,
      ':id' => $id
    );

    $results = QubitPdo::fetchAll($sql, $params, array('fetchMode' => PDO::FETCH_ASSOC));

    // Get non-limited count of resulting rows
    $totalCount = QubitPdo::fetchColumn("select found_rows();");

    $data = array();

    foreach ($results as $result)
    {
      $result['text'] = render_value_inline($result['text']);

      // Overwrite source culture title if the current culture title is populated
      if ($this->getUser()->getCulture() != $result['source_culture'] && !empty($result['current_title']))
      {
        $result['text'] = render_value_inline($result['current_title']);
      }

      // Add identifier based on setting
      if ($this->showIdentifier === 'identifier' && !empty($result['identifier']))
      {
        $result['text'] = $result['identifier'] .' - '. $result['text'];
      }

      // Add reference code based on setting
      if ($this->showIdentifier === 'referenceCode' && !empty($baseReferenceCode))
      {
        if ($result['parent'] == QubitInformationObject::ROOT_ID)
        {
          // If this is a top-level node, the passed reference will be "true" so
          // replace it with the description identifier
          $result['referenceCode'] = render_value_inline($result['identifier']);
        }
        else
        {
          // Start with reference code passed to the function
          $result['referenceCode'] = $baseReferenceCode;

          // Append result identifier to the base reference code if we're not loading the collection root
          //if ($result['parent'] != QubitInformationObject::ROOT_ID && !empty($result['identifier']))

          // Append result identifier, if it exists, to the reference passed to the function
          if (!empty($result['identifier']))
          {
            $result['referenceCode'] = $result['referenceCode'] . sfConfig::get('app_separator_character', '-') . render_value_inline($result['identifier']);
          }
        }

        // Prepend reference code to text
        $result['text'] = "{$result['referenceCode']} - {$result['text']}";
      }

      // Add level of description based on setting
      if (sfConfig::get('app_treeview_show_level_of_description', 'yes') === 'yes' && strlen($result['lod']) > 0)
      {
        $lod = render_value_inline($result['lod']);
        $result['text'] = "[{$lod}] {$result['text']}";
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

          $date = render_value_inline(Qubit::renderDateStartEnd($event['display_date'], $event['start_date'], $event['end_date']));
          $result['text'] = "{$result['text']}, {$date}";

          break;
        }
      }

      if ($result['status_id'] == QubitTerm::PUBLICATION_STATUS_DRAFT_ID)
      {
        $status = render_value_inline($result['status']);
        $result['text'] = "({$status}) {$result['text']}";
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
      $result['a_attr']['title'] = strip_tags($result['text']);
      $result['a_attr']['href'] = $this->generateUrl('slug', array('slug' => $result['slug']));

      // If not a leaf node, indicate node has children
      if ($result['rgt'] - $result['lft'] > 1)
      {
        // Set children to default of true for lazy loading
        $result['children'] = true;

        // If loading an ancestor of the resource, fetch children
        if ($result['lft'] <= $this->resource->lft && $result['rgt'] >= $this->resource->rgt)
        {
          $refCode = '';
          if (!empty($result['referenceCode']))
          {
            $refCode = $result['referenceCode'];
          }

          // If we're not currently fetching children and paging options are set,
          // use paging options when fetching children
          $childOptions = array();
          if (!$children && (isset($options['skip']) || isset($options['limit'])))
          {
            $childOptions = array('skip' => $options['skip'], 'limit' => $options['limit']);
          }

          // Fetch children and note total number of children that exist (useful for paging through children)
          $childData = $this->getNodeOrChildrenNodes($result['id'], $refCode, $children = true, $childOptions);
          $result['children'] = $childData['nodes'];
          $result['total'] = $childData['total'];
        }
      }

      // Not used currently
      $unset = array('lft', 'rgt', 'parent', 'lod', 'status', 'status_id', 'slug', 'source_culture', 'current_title', 'identifier');

      $data[] = array_diff_key($result, array_flip($unset));
    }

    // If not ordered in SQL, order in-memory by "text" attribute of each node
    if (!empty($options['memorySort']))
    {
      $titles = array();
      foreach ($data as $key => $node)
      {
        $titles[$key] = $node['text'];
      }

      usort($data, function($el1, $el2) { return strnatcmp( $el1[‘text’], $el2[‘text’]); });
    }

    return array('nodes' => $data, 'total' => $totalCount);
  }
}
