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
    $start_time = microtime(TRUE);
    $data = array();

    $this->setTemplate(false);
    $this->setLayout(false);
    $this->resource = $request->getAttribute('sf_route')->resource;
    $this->getResponse()->setContentType('application/json');

    $data = $this->getItemIds($this->resource->getCollectionRoot(), !$this->getUser()->user);

    array_walk($data, function(&$data){
      $data['a_attr']['title'] = $data['text'];
      $data['text'] = ((int) $data['status_id'] == QubitTerm::PUBLICATION_STATUS_DRAFT_ID ? '('.$data['status'].') ' : '') . "<u>{$data['type']}</u> {$data['text']}";
      // some special flags on our current active item
      if($data['id'] == $this->resource->id)
      {
        $data['state'] = array('opened' => true, 'selected' => true);
        $data['li_attr'] = array('selected_on_load' => true);
      }

      // set root item's parent to hash symbol for jstree compatibility
      if($data['parent'] == '1') {
        $data['parent'] = '#';
        $data['icon'] = 'fa fa-archive';
      }

      // not used currently
      unset($data['identifier'], $data['status'], $data['status_id']);

      $data['a_attr']['href'] = &$data['slug'];
      unset($data['slug']);

    });

    $end_time = microtime(TRUE);
    return $this->renderText(json_encode(array('core' => array('data' => $data), 'time' => ($end_time - $start_time))));
  }

  protected function getItemIds($item, $drafts = true)
  {
    $drafts_sql = ($drafts) ? "AND status.status_id <> " . QubitTerm::PUBLICATION_STATUS_DRAFT_ID : "" ;
    $sql = "SELECT node.id, 
        i18n.title as text, IFNULL(node.identifier, '') as identifier, 
        node.parent_id as parent, slug.slug, IFNULL(term_type.name, '') as type,
        status_term.name as status, status_id
        FROM 
          (information_object AS parent,
          information_object AS node,
          information_object_i18n as i18n,
          slug, status, term_i18n as status_term) 
          LEFT JOIN term_i18n AS term_type ON (node.level_of_description_id = term_type.id AND term_type.culture = :culture)
        WHERE node.lft BETWEEN parent.lft AND parent.rgt 
          AND node.id = i18n.id
          AND i18n.culture = :culture
          AND status.object_id = node.id
          AND node.id = slug.object_id
          AND parent.id = :id
          AND status_term.id = status.status_id AND status_term.`culture` = :culture
          $drafts_sql
        ORDER BY node.lft;";
    $conn = Propel::getConnection();
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $item->id);
    $stmt->bindValue(':culture', $this->getUser()->getCulture());
    $stmt->execute();

    $ids = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $ids;
  }
}
