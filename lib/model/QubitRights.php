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

class QubitRights extends BaseRights
{
  public function __toString()
  {
    $string = array();

    if (isset($this->basis))
    {
      $string[] = $this->basis;
    }

    if (isset($this->act))
    {
      $string[] = $this->act;
    }

    $string = implode(' - ', $string);

    if (null !== $date = Qubit::renderDateStartEnd(null, $this->startDate, $this->endDate))
    {
      $string .= ' ('.$date.')';
    }

    return $string;
  }

  protected function insert($connection = null)
  {
    $this->slug = QubitSlug::slugify($this->slug);

    return parent::insert($connection);
  }

  public function delete($connection = null)
  {
    // Make sure that the associated QubitRelation object is removed before
    foreach (QubitRelation::getRelationsByObjectId($this->id, array('typeId' => QubitTerm::RIGHT_ID)) as $item)
    {
      $item->indexObjectOnDelete = false;
      $item->delete();
    }

    // remove any related granted rights
    foreach ($this->grantedRights as $gr) {
      $gr->delete();
    }

    parent::delete($connection);
  }

  public function grantedRightsFindById($id)
  {
    foreach($this->grantedRights as $gr)
    {
      if($gr->id === $id)
      {
        return $gr;
      }
    }

    return false;
  }

  public function save($connection = null)
  {
    parent::save($connection);

    // Save updated grantedRights
    foreach ($this->grantedRights as $grantedRight)
    {
      $grantedRight->indexOnSave = false;
      $grantedRight->rights = $this;
      $grantedRight->save();
    }
  }

  public function copy()
  {
    $newRights = new QubitRights;
    $newRights->startDate = $this->startDate;
    $newRights->endDate = $this->endDate;
    $newRights->basis = $this->basis;
    $newRights->rightsHolder = $this->rightsHolder;
    $newRights->copyrightStatus = $this->copyrightStatus;
    $newRights->copyrightStatusDate = $this->copyrightStatusDate;
    $newRights->copyrightJurisdiction = $this->copyrightJurisdiction;
    $newRights->statuteDeterminationDate = $this->statuteDeterminationDate;
    $newRights->statuteCitation = $this->statuteCitation;
    $newRights->sourceCulture = $this->sourceCulture;

    // Current culture row
    $newRights->rightsNote = $this->rightsNote;
    $newRights->copyrightNote = $this->copyrightNote;
    $newRights->identifierValue = $this->identifierValue;
    $newRights->identifierType = $this->identifierType;
    $newRights->identifierRole = $this->identifierRole;
    $newRights->licenseTerms = $this->licenseTerms;
    $newRights->licenseNote = $this->licenseNote;
    $newRights->statuteJurisdiction = $this->statuteJurisdiction;
    $newRights->statuteNote = $this->statuteNote;

    // Other culture rows
    foreach ($this->rightsI18ns as $sourceRightsI18n)
    {
      if (sfContext::getInstance()->user->getCulture() == $sourceRightsI18n->culture)
      {
        continue;
      }

      $rightsI18n = new QubitRightsI18n;
      $rightsI18n->rightsNote = $sourceRightsI18n->rightsNote;
      $rightsI18n->copyrightNote = $sourceRightsI18n->copyrightNote;
      $rightsI18n->identifierValue = $sourceRightsI18n->identifierValue;
      $rightsI18n->identifierType = $sourceRightsI18n->identifierType;
      $rightsI18n->identifierRole = $sourceRightsI18n->identifierRole;
      $rightsI18n->licenseTerms = $sourceRightsI18n->licenseTerms;
      $rightsI18n->licenseNote = $sourceRightsI18n->licenseNote;
      $rightsI18n->statuteJurisdiction = $sourceRightsI18n->statuteJurisdiction;
      $rightsI18n->statuteNote = $sourceRightsI18n->statuteNote;
      $rightsI18n->culture = $sourceRightsI18n->culture;

      $newRights->rightsI18ns[] = $rightsI18n;
    }

    // Copy granted rights
    foreach ($this->grantedRights as $sourceGrantedRight)
    {
      $newGrantedRight = new QubitGrantedRight;
      $newGrantedRight->act = $sourceGrantedRight->act;
      $newGrantedRight->restriction = $sourceGrantedRight->restriction;
      $newGrantedRight->startDate = $sourceGrantedRight->startDate;
      $newGrantedRight->endDate = $sourceGrantedRight->endDate;
      $newGrantedRight->notes = $sourceGrantedRight->notes;
      $newGrantedRight->serialNumber= $sourceGrantedRight->serialNumber;

      $newRights->grantedRights[] = $newGrantedRight;
    }

    $newRights->save();

    return $newRights;
  }
}
