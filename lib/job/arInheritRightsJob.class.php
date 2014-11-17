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
 * A job to make an archival description's children inherit its
 * granted rights. Afterward, the job will initiate another job to
 * calculate new ACL rules based on the granted rights.
 *
 * @package    symfony
 * @subpackage jobs
 */

class arInheritRightsJob extends arBaseJob
{
  public function run($parameters)
  {
    // This will be an array of required parameter names
    $this->addRequiredParameters(array(
      'information_object_id',
      'overwrite_or_combine',  // values: overwrite, combine
      'all_or_digital_only'  // values: all, digital_only
    ));

    // parent::run() will check parameters and throw an exception if any are missing
    parent::run($parameters);

    try
    {
      $ioId = $parameters['information_object_id'];

      if (($io = QubitInformationObject::getById($ioId)) === null)
      {
        $this->error("Invalid information object id: $ioId");
        return false;
      }

      $idsToUpdate = array(); // Info object IDs to recalculate rights based on PREMIS

      foreach ($io->descendants as $descendant)
      {
        // if digital only and descendant isn't a digital object, skip
        if ('digital_only' === $parameters['all_or_digital_only'] && null === $descendant->getDigitalObject())
        {
          $this->info("skipping descendant {$descendant->getId()}\n");
          continue;
        }

        $idsToUpdate[] = $descendant->id;

        // delete existing rights if overwriting rights
        if ('overwrite' === $parameters['overwrite_or_combine']) {
          // the object property of the relation($item) is the right
          foreach ($descendant->getRights() as $item) {
            $item->object->delete();
          }
        }

        // lastly, copy all rights from $io to $descendants
        foreach ($io->getRights() as $parentRelation) {
          $right = $parentRelation->object;
          // duplicate the right
          $newRight = new QubitRights;
          $newRight->startDate                   = $right->startDate;
          $newRight->endDate                     = $right->endDate;
          $newRight->basisId                     = $right->basisId;
          $newRight->rightsHolderId              = $right->rightsHolderId;
          $newRight->copyrightStatusId           = $right->copyrightStatusId;
          $newRight->copyrightStatusDate         = $right->copyrightStatusDate;
          $newRight->copyrightJurisdiction       = $right->copyrightJurisdiction;
          $newRight->statuteDeterminationDate    = $right->statuteDeterminationDate;
          $newRight->sourceCulture               = $right->sourceCulture;

          $newRight->rightsNote                  = $right->rightsNote;
          $newRight->copyrightNote               = $right->copyrightNote;
          $newRight->identifierValue             = $right->identifierValue;
          $newRight->identifierType              = $right->identifierType;
          $newRight->identifierRole              = $right->identifierRole;
          $newRight->licenseTerms                = $right->licenseTerms;
          $newRight->licenseNote                 = $right->licenseNote;
          $newRight->statuteJurisdiction         = $right->statuteJurisdiction;
          $newRight->statuteCitation             = $right->statuteCitation;
          $newRight->statuteNote                 = $right->statuteNote;
          $newRight->culture                     = $right->culture;

          // duplicate the related granted_rights
          foreach($right->grantedRights as $gr)
          {
            $newGr = new QubitGrantedRight;
            $newGr->rightsId      = $gr->rightsId;
            $newGr->actId         = $gr->actId;
            $newGr->restriction   = $gr->restriction;
            $newGr->startDate     = $gr->startDate;
            $newGr->endDate       = $gr->endDate;
            $newGr->notes         = $gr->notes;

            $newRight->grantedRights[] = $newGr;
          }

          $newRight->save();

          // create a relation record associating the new right to the descendant
          $newRelation = new QubitRelation;
          $newRelation->objectId  = $newRight->getId();
          $newRelation->typeId    = QubitTerm::RIGHT_ID;
          $newRelation->subjectId = $descendant->getId();

          $newRelation->save();
        }
      }

      $this->job->setStatusCompleted();
      $this->job->save();
    }
    catch (Exception $e)
    {
      $this->error('Unhandled exception - ' . $e);
      return false;
    }

    return true;
  }
}
