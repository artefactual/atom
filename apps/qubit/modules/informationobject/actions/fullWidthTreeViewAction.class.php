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
    $this->setTemplate(false);
    $this->setLayout(false);

    $this->resource = $request->getAttribute('sf_route')->resource;

    // At this point we don't need to do any ACL check on ancestors
    $this->ancestors = $this->resource->getAncestors()->orderBy('lft');

    // Number of siblings that we are showing above and below the current node
    // It's good to keep this number small since getTreeViewSiblings can be very
    // slow (when sorting by title or identifierTitle)
    $numberOfPreviousOrNextSiblings = 50;

    $this->hasPrevSiblings = false;
    $this->hasNextSiblings = false;

    // Child descriptions
    if ($this->resource->hasChildren())
    {
      list($this->children, $this->hasNextSiblings) = $this->resource->getTreeViewChildren(array('numberOfPreviousOrNextSiblings' => $numberOfPreviousOrNextSiblings));
    }
    // Show siblings if there's no children, but not for root descriptions
    else if (QubitInformationObject::ROOT_ID != $this->resource->parentId)
    {
      // Previous siblings
      // Get an extra sibling just to know if the + button is necessary
      $this->prevSiblings = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'previous'));
      $this->hasPrevSiblings = count($this->prevSiblings) > $numberOfPreviousOrNextSiblings;
      if ($this->hasPrevSiblings)
      {
        array_pop($this->prevSiblings);
      }

      // Reverse array
      $this->prevSiblings = array_reverse($this->prevSiblings);

      // Next siblings, same logic than above with the + button
      $this->nextSiblings = $this->resource->getTreeViewSiblings(array('limit' => $numberOfPreviousOrNextSiblings + 1, 'position' => 'next'));
      $this->hasNextSiblings = count($this->nextSiblings) > $numberOfPreviousOrNextSiblings;
      if ($this->hasNextSiblings)
      {
        array_pop($this->nextSiblings);
      }
    }

    $json = array();
    $json[] = $this->collectPropertiesForJson($this->resource);
    // set as root
    // $json[0]['parent'] = '#';
    $json[0]['state']  = array('selected' => true, 'opened' => true);

    $this->collectToArray($this->nextSiblings, $json);
    $this->collectToArray($this->prevSiblings, $json);
    $this->collectToArray($this->children, $json);
    $this->collectToArray($this->ancestors, $json);



    $this->getResponse()->setContentType('application/json');
    return $this->renderText(json_encode(array('core' => array('data' => $json))));
  }

  protected function collectToArray($collection, &$jsdat)
  {
    foreach($collection as $item)
    {
      $jsdat[] = $this->collectPropertiesForJson($item);
    }
  }

  protected function collectPropertiesForJson($item)
  {
    $title = '';
    if ($item->identifier)
    {
      $title = $item->identifier . "&nbsp;-&nbsp;";
    }
    $title .= $this->render_title($item);

    if(isset($item->levelOfDescription))
    {
      $description = $item->levelOfDescription->__toString();
    } else {
      $description = "";
    }

    return array(
      'id' => $item->getPrimaryKey(), 
      'parent' => ($item->parentId ? $item->parentId : '#'), 
      'text' => $title,
      'a_attr' => array("href" => $this->generateUrl() . $item->slug),
      'icon' => $description,
      'pub_status' =>  $item->getPublicationStatus()->__toString(),
    );
  }

  protected function render_title($value)
  {
    // TODO Workaround for PHP bug, http://bugs.php.net/bug.php?id=47522
    // Also, method_exists is very slow if a string is passed (class lookup), use is_object
    if (is_object($value) && method_exists($value, '__toString'))
    {
      $value = $value->__toString();
    }

    if (0 < strlen($value))
    {
      return (string) $value;
    }

    return (sfContext::getInstance()->i18n->__('Untitled'));
  }
}
