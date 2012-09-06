<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Digital Object - Update database from "edit" form
 *
 * @package    qubit
 * @subpackage digitalobject
 * @author     david juhasz <david@artefactual.com>
 * @version    SVN: $Id
 *
 */
class DigitalObjectUpdateAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Check user authorization
    if (!QubitAcl::check($this->resource->informationObject, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }

    // Set the digital object's attributes
    $this->resource->usageId = $request->usage_id;
    $this->resource->mediaTypeId = $request->media_type_id;

    // Save the digital object
    $this->resource->save();

    // Return to edit page
    $this->redirect('digitalobject/edit?id='.$this->resource->id);
  }
}
