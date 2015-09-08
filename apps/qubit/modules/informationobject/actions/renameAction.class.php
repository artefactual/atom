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

class InformationObjectRenameAction extends sfAction
{
  // Allow modification of title, slug, and digital object filename
  public function execute($request)
  {
    // Return 400 if incorrect HTTP method
    if ($request->isMethod('post'))
    {
      $resource = $this->getRoute()->resource;

      $renameComponent = new InformationObjectRenameComponent($this->context, 'informationobject', 'index');
      $renameComponent->resource = $resource;
      $renameComponent->execute($request);

      $this->form = $renameComponent->form;

      $this->form->bind($request->rename);

      if ($this->form->isValid())
      {
        // Internationalization needed for flash messages
        ProjectConfiguration::getActive()->loadHelpers('I18N');

        // Let user know description was updated (and if slug had to be adjusted)
        $message = __('Description updated.');

        $postedSlug = $this->form->getValue('slug');

        if ((null !== $postedSlug) && $resource->slug != $postedSlug)
        {
          $message .= ' '. __('Slug was adjusted to remove special characters or because it has already been used for another description.');
        }

        $this->getUser()->setFlash('notice', $message);

        $this->redirect(array($resource, 'module' => 'informationobject'));
      }
    }
    else
    {
      $this->response->setStatusCode(400);
      return sfView::NONE;
    }
  }
}
