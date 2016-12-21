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

class EventIndexAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->getUser()->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    $this->resource = $this->getRoute()->resource;

    $value = array();

    if (isset($this->resource->actor))
    {
      $value['actor'] = $this->context->routing->generate(null, array($this->resource->actor, 'module' => 'actor'));
      $value['actorDisplay'] = $this->resource->actor->getAuthorizedFormOfName(array('cultureFallback' => true));
    }

    if (isset($this->resource->date))
    {
      $value['date'] = $this->resource->date;
    }

    $value['endDate'] = Qubit::renderDate($this->resource->endDate);
    $value['startDate'] = Qubit::renderDate($this->resource->startDate);

    if (isset($this->resource->description))
    {
      $value['description'] = $this->resource->description;
    }

    if (isset($this->resource->object))
    {
      $value['informationObject'] = $this->context->routing->generate(null, array($this->resource->object, 'module' => 'informationobject'));
    }

    $place = $this->resource->getPlace();
    if (isset($place))
    {
      $value['place'] = $this->context->routing->generate(null, array($place, 'module' => 'term'));
      $value['placeDisplay'] = $place->getName(array('cultureFallback' => true));
    }

    if (isset($this->resource->type))
    {
      $value['type'] = $this->context->routing->generate(null, array($this->resource->type, 'module' => 'term'));
    }

    return $this->renderText(json_encode($value));
  }
}
