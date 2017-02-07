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
 * Digital Object deletion
 *
 * @package    AccesstoMemory
 * @subpackage digitalObject
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm();

    $this->resource = $this->getRoute()->resource;

    // Get related information object by first grabbing top-level digital
    // object
    $parent = $this->resource->parent;
    if (isset($parent))
    {
      $this->informationObject = $parent->informationObject;
    }
    else
    {
      $this->informationObject = $this->resource->informationObject;
      if (!isset($this->informationObject))
      {
        $this->forward404();
      }
    }

    // Check user authorization
    if (!QubitAcl::check($this->informationObject, 'delete'))
    {
      QubitAcl::forwardUnauthorized();
    }

    if ($request->isMethod('delete'))
    {
      // Delete the digital object record from the database
      $this->resource->delete();
      QubitSearch::getInstance()->update($this->informationObject);
      $this->informationObject->updateXmlExports();

      // Redirect to edit page for parent Info Object
      if (isset($parent))
      {
        $this->redirect(array($parent, 'module' => 'digitalobject', 'action' => 'edit'));
      }
      else
      {
        $this->redirect(array($this->informationObject, 'module' => 'informationobject'));
      }
    }
  }
}
