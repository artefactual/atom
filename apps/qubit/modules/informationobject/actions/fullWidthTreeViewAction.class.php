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

class InformationObjectFullWidthTreeViewAction extends sfAction
{
  public function execute($request)
  {
    $start_time = microtime(TRUE);
    $data = array();

    $this->setTemplate(false);
    $this->setLayout(false);
    $this->resource = $request->getAttribute('sf_route')->resource;
    $this->getResponse()->setContentType('application/json');

    $data = $this->getItemIds($this->resource->getCollectionRoot());

    array_walk($data, function(&$data){
      // some special flags on our current active item
      if($data['id'] == $this->resource->id)
      {
        $data['state'] = array('opened' => true, 'selected' => true);
        $data['li_attr'] = array('selected_on_load' => true);
      }

      // set root item's parent to hash symbol for jstree compatibility
      if($data['parent'] == '1') {
        $data['parent'] = '#';
      }

      // populate columns
      $data['data']['identifier'] = &$data['identifier'];
      $data['data']['type']       = &$data['type'];
      $data['data']['status']     = &$data['status'];
      unset($data['identifier']);

      switch ($data['type']) {
        case 'Collection':
        case 'Fonds':
        case 'Subfonds':
          $data['icon'] = 'fa fa-archive';
          break;
        case 'File':
        case 'Series':
        case 'Subseries':
          $data['icon'] = 'fa fa-folder-o';
          break;
        case 'Item':
          $data['icon'] = 'fa fa-file-o';
          break;
        
        default:
          # code...
          break;
      }

      $data['a_attr']['href'] = &$data['slug'];
      unset($data['slug']);

    });

    $end_time = microtime(TRUE);
    return $this->renderText(json_encode(array('core' => array('data' => $data), 'time' => ($end_time - $start_time))));
  }

  protected function getItemIds($item, $down = true)
  {
    // Depending on if we want the ancestor tree or 
    // the child tree we can just flip whether we 
    // return parent's properties or node's properties
    // rnode = relation node
    //    the object node at the center of this query
    // qnode = query node
    //    the objects properties we want

    list($rnode, $qnode) = ($down) ? array('parent', 'node') : array('node','parent');
    $sql = "SELECT $qnode.id, 
        i18n.title as text, $qnode.identifier as identifier, 
        $qnode.parent_id as parent, slug.slug, term_i18n.name as type,
        status_term.name as status
        FROM 
          information_object AS parent,
          information_object AS node,
          information_object_i18n as i18n,
          term, term_i18n, slug, status, term_i18n as status_term
        WHERE node.lft BETWEEN parent.lft AND parent.rgt 
          AND $qnode.id = i18n.id
          AND $qnode.level_of_description_id = term.id
          AND term.id = term_i18n.id
          AND term_i18n.culture = 'en'
          AND i18n.culture = 'en'
          AND status.object_id = node.id
          AND $qnode.id = slug.object_id
          AND $rnode.id = :id
          AND status_term.id = status.status_id AND status_term.`culture` = 'en'
        ORDER BY node.lft;";
    $conn = Propel::getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $item->id);
    $stmt->execute();

    $ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $ids;
  }
}
