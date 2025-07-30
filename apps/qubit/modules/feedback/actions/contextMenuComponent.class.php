<?php

/**
 * Bookout Object edit component.
 *
 * @package    qubit
 * @subpackage Preservation Module
 * @author     Ramaano Ndou <ramaano.ndou@sita.co.za>
 * @version    SVN: $Id
 */
 
class BookoutObjectContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    $this->bookoutObjects = array();
    foreach (QubitRelation::getRelatedSubjectsByObjectId('QubitBookoutObject', $this->resource->id, array('requestorId' => QubitTaxonomy::BOOKOUT_TYPE_ID)) as $item)

    {
      $this->bookoutObjects[$item->id] = $item;
    }

    if (1 > count($this->bookoutObjects))
    {
      return sfView::NONE;
    }
  }
}
