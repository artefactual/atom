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
class InformationObjectFullWidthTreeViewAction extends DefaultFullTreeViewAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      QubitAcl::forwardUnauthorized();
    }

    $baseReferenceCode = '';

    // On first load, retrieve the ancestors of the selected resource, the resource
    // and its children, otherwise get only the resource's children
    if (filter_var($request->getParameter('firstLoad', false), FILTER_VALIDATE_BOOLEAN)
      && null !== $collectionRoot = $this->resource->getCollectionRoot())
    {
      if ($this->showIdentifier === 'referenceCode')
      {
        // On first load, get the base reference code from the ORM
        $baseReferenceCode = $collectionRoot->getInheritedReferenceCode();
      }

      $data = $this->getNodeOrChildrenNodes($collectionRoot->id, $baseReferenceCode);
    }
    else
    {
      if ($this->showIdentifier === 'referenceCode')
      {
        // Try to get the resource's reference code from the request
        $baseReferenceCode = $request->getParameter('referenceCode');
        if (empty($baseReferenceCode))
        {
          // Or get it from the ORM
          $baseReferenceCode = $this->resource->getInheritedReferenceCode();
        }
      }

      $data = $this->getNodeOrChildrenNodes($this->resource->id, $baseReferenceCode, $children = true);
    }

    return $this->renderText(json_encode($data['nodes']));
  }
}
