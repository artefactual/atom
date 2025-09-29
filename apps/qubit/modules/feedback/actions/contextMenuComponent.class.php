<?php

/**
 * Bookout Object edit component.
 *
 * @author     Ramaano Ndou <ramaano.ndou@sita.co.za>
 *
 * @version    SVN: $Id
 */
class BookoutObjectContextMenuComponent extends sfComponent
{
  public function execute($request)
  {
    $this->resource = $request->getAttribute('sf_route')->resource;

    $this->bookoutObjects = [];
    foreach (QubitRelation::getRelatedSubjectsByObjectId('QubitBookoutObject', $this->resource->id, ['requestorId' => QubitTaxonomy::BOOKOUT_TYPE_ID]) as $item) {
      $this->bookoutObjects[$item->id] = $item;
    }

    if (1 > count($this->bookoutObjects)) {
      return sfView::NONE;
    }
  }
}
