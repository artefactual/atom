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
 * Get JSON representation of relation
 *
 * @package qubit
 * @subpackage relation
 * @author     David Juhasz <david@artefactual.com>
 */
class RelationIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    $value = array();

    $value['date'] = $this->resource->date;
    $value['endDate'] = Qubit::renderDate($this->resource->endDate);
    $value['startDate'] = Qubit::renderDate($this->resource->startDate);
    $value['description'] = $this->resource->description;

    if (isset($this->resource->object))
    {
      $value['object'] = $this->context->routing->generate(null, array($this->resource->object));
    }

    if (isset($this->resource->subject))
    {
      $value['subject'] = $this->context->routing->generate(null, array($this->resource->subject));
    }

    if (isset($this->resource->type))
    {
      $value['type'] = $this->context->routing->generate(null, array($this->resource->type, 'module' => 'term'));
    }

    if (method_exists($this, 'extraQueries'))
    {
      $value = $this->extraQueries($value);
    }

    return $this->renderText(json_encode($value));
  }
}
