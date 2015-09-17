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
    if ($request->isMethod('delete'))
    {
      $this->context->user->getClipboard()->clear();

      if ($request->isXmlHttpRequest())
      {
        $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

        return $this->renderText(json_encode(array('success' => true)));
      }

      $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
    }

    $slugs = $this->context->user->getClipboard()->getAll();

    // Redirect to clipboard page if the clipboard is empty
    if (count($slugs) == 0)
    {
      $this->redirect(array('module' => 'user', 'action' => 'clipboard'));
    }

    // Get all descriptions added to the clipboard
    $query = new \Elastica\Query(new \Elastica\Query\Terms('slug', $slugs));
    $query->setLimit(count($slugs));

    // Filter drafts in case they were manually added
    $filterBool = new \Elastica\Filter\Bool;
    QubitAclSearch::filterDrafts($filterBool);
    if (0 < count($filterBool->toArray()))
    {
      $query->setFilter($filterBool);
    }

    $this->resultSet = QubitSearch::getInstance()->index->getType('QubitInformationObject')->search($query);

    $this->form = new sfForm;
  }
}
