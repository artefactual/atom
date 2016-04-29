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
    $data = array();

    $this->setTemplate(false);
    $this->setLayout(false);
    $this->resource = $request->getAttribute('sf_route')->resource;
    $this->getResponse()->setContentType('application/json');

    // Viewing a description with a restricted level of description (folder, item or part)
    // the treeview will expand to and select the first non restricted ancestor
    $this->selectedItemId = $this->resource->id;

    $data = $this->getItemIds($this->resource->getCollectionRoot(), !$this->getUser()->user);

    // Alias and pass in $this as $_this because php 5.3 dosn't support
    // referencing $this in anonymouns functions (fixed in php5.4)
    $_this =& $this;

    array_walk($data, function(&$data) use ($_this)
    {
      // Overwrite source culture title if the current culture title is populated
      if ($this->getUser()->getCulture() != $data['source_culture']
        && !empty($data['current_title']))
      {
        $data['text'] = $data['current_title'];
      }

      $data['a_attr']['title'] = $data['text'];
      $data['text'] = ((int) $data['status_id'] == QubitTerm::PUBLICATION_STATUS_DRAFT_ID ? '('.$data['status'].') ' : '') . "<u>{$data['type']}</u> {$data['text']}";

      // Some special flags on our current selected item
      if ($data['id'] == $_this->selectedItemId)
      {
        $data['state'] = array('opened' => true, 'selected' => true);
        $data['li_attr'] = array('selected_on_load' => true);
      }

      // Set root item's parent to hash symbol for jstree compatibility
      if ($data['parent'] == '1')
      {
        $data['parent'] = '#';
        $data['icon'] = 'fa fa-archive';
      }

      // Not used currently
      unset($data['identifier'], $data['status'], $data['status_id'], $data['source_culture'], $data['current_title']);

      $data['a_attr']['href'] = $_this->generateUrl('slug', array('slug' => @$data['slug']));
      unset($data['slug']);
    });

    return $this->renderText(json_encode(array('core' => array('data' => $data))));
  }

  protected function getItemIds($item, $drafts = true)
  {
    $i18n = sfContext::getInstance()->i18n;
    $untitled = $i18n->__('Untitled');

    $draftsSql = ($drafts) ? "AND status.status_id <> " . QubitTerm::PUBLICATION_STATUS_DRAFT_ID : "" ;

    $sql = "SELECT node.id, node.source_culture,
        IFNULL(source_i18n.title, '<i>$untitled</i>') as text, IFNULL(node.identifier, '') as identifier,
        node.parent_id as parent, slug.slug, IFNULL(term_type.name, '') as type,
        status_term.name as status, status_id, current_i18n.title as current_title
        FROM
          (information_object AS parent,
          information_object AS node,
          information_object_i18n as source_i18n,
          slug, status, term_i18n as status_term)
          LEFT JOIN term_i18n AS term_type ON (node.level_of_description_id = term_type.id AND term_type.culture = :culture)
          LEFT JOIN information_object_i18n AS current_i18n ON (node.id = current_i18n.id AND current_i18n.culture = :culture)
        WHERE node.lft BETWEEN parent.lft AND parent.rgt
          AND source_i18n.id = node.id
          AND source_i18n.culture = node.source_culture
          AND status.object_id = node.id
          AND node.id = slug.object_id
          AND parent.id = :id
          AND status_term.id = status.status_id AND status_term.`culture` = :culture
          $draftsSql
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
