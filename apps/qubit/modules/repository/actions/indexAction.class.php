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

class RepositoryIndexAction extends sfAction
{
  public function execute($request)
  {
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

    $this->dispatcher->notify(new sfEvent($this, 'access_log.view', array('object' => $this->resource)));

    // Per-institution stylesheet
    if (file_exists(sfConfig::get('sf_upload_dir').'/r/'.$this->resource->slug.'/conf/style.css'))
    {
      $this->response->addStyleSheet('/uploads/r/'.$this->resource->slug.'/conf/style.css', 'last', array('media' => 'all'));
    }

    // Primary contact
    $this->primaryContact = $this->resource->getPrimaryContact();
  }
}
