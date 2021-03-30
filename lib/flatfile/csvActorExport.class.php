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
 * Export flatfile actor data.
 */
class csvActorExport extends QubitFlatfileExport
{
    protected $options = [];

    /*
     * Store export parameters for use.
     *
     * @return void
     */
    public function setOptions($options = [])
    {
        $this->options = $options;
    }

    /**
     * Export a actor, and additionally any aliases / relationships.
     *
     * @param object $resource object to export
     */
    public function exportResource(&$resource)
    {
        parent::exportResource($resource);

        // Export relations
        $filenamePrepend = (null !== $this->standard) ? $this->standard.'_' : '';
        $filename = sprintf(
            '%s/%s%s_%s.csv',
            $this->path,
            $filenamePrepend,
            str_pad($this->fileIndex, 10, '0', STR_PAD_LEFT),
            'relations'
        );

        $this->exportRelations($filename, $resource);
    }

    /*
     * Specific column settings before CSV row write
     *
     * @return void
     */
    protected function modifyRowBeforeExport()
    {
        // Set common column values
        parent::modifyRowBeforeExport();

        $this->setColumn('parallelFormsOfName', $this->getNames(QubitTerm::PARALLEL_FORM_OF_NAME_ID));
        $this->setColumn('standardizedFormsOfName', $this->getNames(QubitTerm::STANDARDIZED_FORM_OF_NAME_ID));
        $this->setColumn('otherFormsOfName', $this->getNames(QubitTerm::OTHER_FORM_OF_NAME_ID));

        $this->setMaintenanceNote();
        $this->setOccupations();
        $this->setPlaceAccessPoints();
        $this->setSubjectAccessPoints();
    }

    private function exportRelations($filename, $resource)
    {
        $rows = [];

        foreach ($resource->getActorRelations() as $relation) {
            $relatedEntity = $relation->getOpposedObject($resource->id);

            // Take note of relationship type
            $relationType = $relation->type;

            /* If the current actor being exported is the object, rather than subject, of a
               relation then we check for a converse relationship type, if any, to put in
               the "relationType" column.

               For example if the current actor is the object of a "controls" type relation
               (where the subject "controls" the object) then the converse relationship type
               would be "is controlled by".

               Some relation types like "is the sibling of", however, are reciprical and
               don't have converse types. A lookup of the converse type for this relation type
               will return null.
            */
            if ($relation->objectId == $resource->id) {
                $converseRelation = $relationType->getConverseActorRelationTerm();
                $relationType = (empty($converseRelation)) ? $relationType : $converseRelation;
            }

            $rows[] = [
                'subjectAuthorizedFormOfName' => $relatedEntity->authorizedFormOfName,
                'relationType' => (string) $relationType, // Return string representation for QubitTerm
                'objectAuthorizedFormOfName' => $resource->authorizedFormOfName,
                'description' => $relation->description,
                'date' => $relation->date,
                'startDate' => $relation->startDate,
                'endDate' => $relation->endDate,
                'culture' => $resource->culture,
            ];
        }

        $this->appendToCompanionCsv($filename, $rows);
    }

    private function appendToCompanionCsv($filename, array $rows)
    {
        if (empty($rows)) {
            return;
        }

        if (false === $fh = fopen($filename, 'a')) {
            throw new sfException("Failed to create/open file {$filename}");
        }

        if (!filesize($filename)) {
            // Write header if file's newly created
            fputcsv($fh, array_keys($rows[0]));
        }

        foreach ($rows as $row) {
            fputcsv($fh, $row);
        }

        fclose($fh);
    }

    private function setMaintenanceNote()
    {
        $criteria = new Criteria();
        $criteria->add(QubitNote::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitNote::TYPE_ID, QubitTerm::MAINTENANCE_NOTE_ID);

        if (null !== $note = QubitNote::getOne($criteria)) {
            $this->setColumn('maintenanceNotes', (string) $note);
        }
    }

    private function setOccupations()
    {
        $addNotes = false;
        $actorOccupations = $actorOccupationNotes = [];

        foreach ($this->resource->getOccupations() as $occupation) {
            $actorOccupations[] = (string) $occupation->term;

            $note = $occupation->getNotesByType([
                'noteTypeId' => QubitTerm::ACTOR_OCCUPATION_NOTE_ID,
            ])->offsetGet(0);

            if (isset($note)) {
                $addNotes = true;
                $actorOccupationNotes[] = (string) $note->content;
            } else {
                $actorOccupationNotes[] = 'NULL';
            }
        }

        $this->setColumn('actorOccupations', implode('|', $actorOccupations));

        if ($addNotes) {
            $this->setColumn('actorOccupationNotes', implode('|', $actorOccupationNotes));
        }
    }

    /*
     * Get place access point data
     *
     * @return void
     */
    private function setPlaceAccessPoints()
    {
        $accessPoints = $this->resource->getPlaceAccessPoints();

        $data = [];
        $data['names'] = [];

        foreach ($accessPoints as $accessPoint) {
            if ($accessPoint->term->name) {
                $data['names'][] = $accessPoint->term->name;
            }
        }

        $this->setColumn('placeAccessPoints', implode('|', $data['names']));
    }

    /*
     * Get subject access point data
     *
     * @return void
     */
    private function setSubjectAccessPoints()
    {
        $accessPoints = $this->resource->getSubjectAccessPoints();

        $data = [];
        $data['names'] = [];

        foreach ($accessPoints as $accessPoint) {
            if ($accessPoint->term->name) {
                $data['names'][] = $accessPoint->term->name;
            }
        }

        $this->setColumn('subjectAccessPoints', implode('|', $data['names']));
    }

    /*
     * Get alternative forms of name
     *
     * @return array  List of names
     */
    private function getNames($typeId)
    {
        $results = [];

        foreach ($this->resource->getOtherNames(['typeId' => $typeId]) as $name) {
            $results[] = $name->getName();
        }

        return $results;
    }
}
