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

class RightIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    $value = array();

    if (isset($this->resource->act))
    {
      $value['act'] = $this->context->routing->generate(null, array($this->resource->act, 'module' => 'term'));
    }

    $value['restriction'] = $this->resource->restriction;

    $value['startDate'] = Qubit::renderDate($this->resource->startDate);

    $value['endDate'] = Qubit::renderDate($this->resource->endDate);

    if (isset($this->resource->rightsHolder))
    {
      $value['rightsHolder'] = $this->context->routing->generate(null, array($this->resource->rightsHolder, 'module' => 'rightsholder'));
    }

    if (isset($this->resource->rightsNote))
    {
      $value['rightsNote'] = $this->resource->rightsNote;
    }

    if (isset($this->resource->basis))
    {
      $value['basis'] = $this->context->routing->generate(null, array($this->resource->basis, 'module' => 'term'));
    }

    /**
     * Basis: copyright
     */

    if (isset($this->resource->copyrightStatus))
    {
      $value['copyrightStatus'] = $this->context->routing->generate(null, array($this->resource->copyrightStatus, 'module' => 'term'));
    }

    if (isset($this->resource->copyrightStatusDate))
    {
      $value['copyrightStatusDate'] = $this->resource->copyrightStatusDate;
    }

    if (isset($this->resource->copyrightJurisdiction))
    {
      $value['copyrightJurisdiction'] = $this->resource->copyrightJurisdiction;
    }

    if (isset($this->resource->copyrightNote))
    {
      $value['copyrightNote'] = $this->resource->copyrightNote;
    }

    /**
     * Basis: license
     */

    if (isset($this->resource->licenseIdentifier))
    {
      $value['licenseIdentifier'] = $this->resource->licenseIdentifier;
    }

    if (isset($this->resource->licenseTerms))
    {
      $value['licenseTerms'] = $this->resource->licenseTerms;
    }

    if (isset($this->resource->licenseNote))
    {
      $value['licenseNote'] = $this->resource->licenseNote;
    }

    /**
     * Basis: statute
     */

    if (isset($this->resource->statuteJurisdiction))
    {
      $value['statuteJurisdiction'] = $this->resource->statuteJurisdiction;
    }

    if (isset($this->resource->statuteCitation))
    {
      $value['statuteCitation'] = $this->resource->statuteCitation;
    }

    if (isset($this->resource->statuteDeterminationDate))
    {
      $value['statuteDeterminationDate'] = $this->resource->statuteDeterminationDate;
    }

    if (isset($this->resource->statuteNote))
    {
      $value['statuteNote'] = $this->resource->statuteNote;
    }

    return $this->renderText(json_encode($value));
  }
}
