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
 * Export flatfile accession data.
 *
 * @author     Daniel Lovegrove <d.lovegrove11@gmail.com>
 */
class csvAccessionExport extends QubitFlatfileExport
{
    // Taxonomy cache properties - these must also be defined in the yml file under cacheTaxonomies
    protected $alternateIdTypes = [];
    protected $eventTypeTerms = [];
    protected $physicalObjectTypes = [];
    protected $resourceTypeTerms = [];
    protected $acquisitionTypeTerms = [];
    protected $priorityTerms = [];
    protected $statusTerms = [];

    // Defined in taxonomyMap in yml file. Describes how to map from taxonomy properties to columns
    protected $taxonomyMap = [];

    // Mapping from donor properties to columns
    protected $contactInfoMap = [];

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

    protected function config(&$config)
    {
        // Store taxonomy mappings
        foreach ($config['taxonomyMap'] as $property => $obj) {
            $this->taxonomyMap[$obj['column']] = [
                'property' => $property,
                'terms' => $this->{$obj['terms']},
            ];
        }

        $this->contactInfoMap = $config['contactInfoMap'];
    }

    /*
     * Make modifications to the row before it is written to the output. These operations can't be
     * handled with a simple mapping, and must be populated manually.
     *
     * @return void
     */
    protected function modifyRowBeforeExport()
    {
        parent::modifyRowBeforeExport();
        $this->setAlternativeIdentifierColumns();
        $this->setEventsAndActors();
        $this->setTaxonomies();
        $this->setPhysicalObjectInfo();
        $this->setDonorInfo();
    }

    /*
     * Sets these columns:
     *
     * alternativeIdentifiers
     * alternativeIdentifierTypes
     * alternativeIdentifierNotes
     *
     * @return void
     */
    protected function setAlternativeIdentifierColumns()
    {
        $altIds = $this->resource->getAlternativeIdentifiers();

        $alternativeIdentifiers = [];
        $alternativeIdentifierTypes = [];
        $alternativeIdentifierNotes = [];

        foreach ($altIds as $altId) {
            $alternativeIdentifiers[] = $altId->name;
            $alternativeIdentifierTypes[] = $this->alternateIdTypes[$altId->typeId] ?? '';
            $alternativeIdentifierNotes[] = $altId->note ?? '';
        }

        $this->setColumn('alternativeIdentifiers', $alternativeIdentifiers);
        $this->setColumn('alternativeIdentifierTypes', $alternativeIdentifierTypes);
        $this->setColumn('alternativeIdentifierNotes', $alternativeIdentifierNotes);
    }

    /*
     * Sets these columns:
     *
     * eventActors
     * eventTypes
     * eventDates
     * eventStartDates
     * eventEndDates
     *
     * @return void
     */
    protected function setEventsAndActors()
    {
        $events = $this->resource->getEventsRelatedByobjectId();

        $actors = [];
        $types = [];
        $dates = [];
        $startDates = [];
        $endDates = [];

        foreach ($events as $event) {
            $actors[] = $event->actor->authorizedFormOfName ?? 'NULL';
            $types[] = $this->eventTypeTerms[$event->typeId] ?? 'NULL';
            $dates[] = $event->date ?? 'NULL';
            $startDates[] = $event->startDate ?? 'NULL';
            $endDates[] = $event->endDate ?? 'NULL';
        }

        $this->setColumn('eventActors', $actors);
        $this->setColumn('eventTypes', $types);
        $this->setColumn('eventDates', $dates);
        $this->setColumn('eventStartDates', $startDates);
        $this->setColumn('eventEndDates', $endDates);
    }

    /*
     * Sets the columns defined in taxonomyColumnMap. Leaves column empty if no taxonomy exists.
     *
     * @return void
     */
    protected function setTaxonomies()
    {
        foreach ($this->taxonomyMap as $column => $map) {
            $terms = $map['terms'];
            $id = $this->resource->{$map['property']};
            if ($id && $terms && array_key_exists($id, $terms) && $terms[$id]) {
                $this->setColumn($column, $terms[$id]);
            }
        }
    }

    /*
     * Sets these columns:
     *
     * physicalObjectName
     * physicalObjectLocation
     * physicalObjectType
     *
     * @return void
     */
    protected function setPhysicalObjectInfo()
    {
        $physicalObjects = $this->resource->getPhysicalObjects();

        $physicalObjectNames = [];
        $physicalObjectLocations = [];
        $physicalObjectTypes = [];

        foreach ($physicalObjects as $physicalObject) {
            $physicalObjectNames[] = $physicalObject->name;
            $physicalObjectLocations[] = $physicalObject->location;
            $physicalObjectTypes[] = $this->physicalObjectTypes[$physicalObject->typeId];
        }

        $this->setColumn('physicalObjectName', $physicalObjectNames);
        $this->setColumn('physicalObjectLocation', $physicalObjectLocations);
        $this->setColumn('physicalObjectType', $physicalObjectTypes);
    }

    /**
     * Sets these columns:
     *
     * donorName
     *
     * As well as the columns defined in the contactInfoMap in the yml file.
     */
    protected function setDonorInfo()
    {
        $query = QubitRelation::getRelationsBySubjectId($this->resource->id, ['typeId' => QubitTerm::DONOR_ID]);

        if (!$query) {
            return;
        }

        $updates = ['donorName' => []];
        $multipleDonors = count($query) > 1;

        foreach ($query as $item) {
            $donor = $item->object;
            $updates['donorName'][] = $donor->authorizedFormOfName;
            $contactInfo = $donor->getPrimaryContact();

            foreach ($this->contactInfoMap as $prop => $col) {
                if (!array_key_exists($col, $updates)) {
                    $updates[$col] = [];
                }

                if ($contactInfo->{$prop}) {
                    $updates[$col][] = $contactInfo->{$prop};
                }
                // Only include NULL if there are multiple donors
                elseif ($multipleDonors) {
                    $updates[$col][] = 'NULL';
                }
            }
        }

        foreach ($updates as $col => $newElements) {
            $this->setColumn($col, $newElements);
        }
    }
}
