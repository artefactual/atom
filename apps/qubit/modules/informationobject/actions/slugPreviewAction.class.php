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

class InformationObjectSlugPreviewAction extends sfAction
{
  // Provide a preview of what a slug could be renamed to, given a title
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // Return 401 if unauthorized
    if (!sfContext::getInstance()->user->isAuthenticated()
      || !QubitAcl::check($this->resource, 'read'))
    {
      $this->response->setStatusCode(401);
      return sfView::NONE;
    }

    // Return JSON containing first available slug
    $availableSlug = $this->determineAvailableSlug($this->request->getParameter('title'), $this->resource->id);

    $response = array(
      'slug' => $availableSlug,
      'adjusted' => $availableSlug != QubitSlug::slugify($this->request->getParameter('title'))
    );

    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    return $this->renderText(json_encode($response));
  }

  public static function determineAvailableSlug($title, $resourceId)
  {
    $originalTitle = $title;

    do
    {
      $slugText = QubitSlug::slugify($title);

      $criteria = new Criteria;
      $criteria->add(QubitSlug::SLUG, $slugText);

      // Create title variant in case current isn't available
      $counter++;
      $title = $originalTitle .'-' . $counter;

      $slug = QubitSlug::getOne($criteria);
    }
    while (($slug != null) && ($slug->objectId != $resourceId));

    return $slugText;
  }
}
