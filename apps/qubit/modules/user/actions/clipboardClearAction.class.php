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

class UserClipboardClearAction extends sfAction
{
  public function execute($request)
  {
    $this->type = $request->getGetParameter('type', null);

    if ($request->isMethod('delete'))
    {
      $this->context->user->getClipboard()->clear($this->type);

      if ($request->isXmlHttpRequest())
      {
        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode(array('success' => true)));
      }

      $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
    }

    $allSlugs = $this->context->user->getClipboard()->getAllByClassName();
    $slugs = $allSlugs[$this->type];
    $this->typeLabel = $this->getTypeLabel();

    // Redirect to clipboard page if the clipboard is empty
    if (count($slugs) == 0)
    {
      $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
    }

    // Get all descriptions added to the clipboard
    $query = new \Elastica\Query;
    $queryTerms = new \Elastica\Query\Terms('slug', $slugs);
    $queryBool = new \Elastica\Query\BoolQuery;
    $queryBool->addMust($queryTerms);

    if ($this->type == 'QubitInformationObject')
    {
      QubitAclSearch::filterDrafts($queryBool);
    }

    $query->setQuery($queryBool);
    $query->setSize(count($slugs));

    $this->resultSet = QubitSearch::getInstance()->index->getType($this->type)->search($query);

    $this->form = new sfForm;
  }

  /**
   * Return human readable entity type label from config.
   */
  private function getTypeLabel()
  {
    switch ($this->type)
    {
      case 'QubitInformationObject':
        return sfConfig::get('app_ui_label_informationobject');
      case 'QubitActor':
        return sfConfig::get('app_ui_label_actor');
      case 'QubitRepository':
        return sfConfig::get('app_ui_label_repository');
      default:
        throw new sfException("Invalid entity type in clear clipboard: {$this->type}");
    }
  }
}
