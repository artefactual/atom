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
 * Digital Object view action
 *
 * @package    AccesstoMemory
 * @subpackage digital object
 * @author     Andy Koch <koch.andy@gmail.com>
 */
class DigitalObjectViewAction extends sfAction
{
  public function execute($request)
  {
    $pathinfo = pathinfo($request->getPathInfo());
    $pathinfo['dirname'] = str_replace("/{$request->module}/{$request->action}", '', $pathinfo['dirname']).'/';

    $this->resource = QubitDigitalObject::getByPathFile($pathinfo['dirname'], $pathinfo['basename']);

    // Resource Found?
    if (null === $this->resource)
    {
      $this->forward404();
    }

    list($infoObj, $action) = $this->getInfoObjAndAction();

    // Do appropriate ACL check(s)
    if (!QubitAcl::check($infoObj, $action) || !QubitGrantedRight::checkPremis($infoObj->id, $action))
    {
      $this->forward404();
    }

    $this->getResponse()->setContentType($this->resource->mimeType);
    $this->getResponse()->setHttpHeader('X-Accel-Redirect', '/private' . $this->resource->getFullPath());

    return sfView::HEADER_ONLY;
  }

  private function getInfoObjAndAction()
  {
    switch ($this->resource->usageId)
    {
      case QubitTerm::MASTER_ID:
        $action = 'readMaster';
        $infoObj = $this->resource->informationObject;
        break;

      case QubitTerm::REFERENCE_ID:
        $action = 'readReference';
        $infoObj = $this->resource->parent->informationObject;
        break;

      case QubitTerm::THUMBNAIL_ID:
        $action = 'readThumbnail';
        $infoObj = $this->resource->parent->informationObject;
        break;

      default:
        throw new sfException("Invalid usageId given in digitalobject/view: {$this->resource->usageId}");
    }

    return array($infoObj, $action);
  }
}
