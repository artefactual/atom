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
 * Export flatfile term data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class csvTermExport extends QubitFlatfileExport
{
    // Taxonomy cache properties
    protected $commonNoteTypeIds = [];

    /*
     * Term-specific property setting based on configuration data
     *
     * @return void
     */
    protected function config(&$config)
    {
        // Store note mappings
        $this->commonNoteMap = $config['note']['common'];
    }

    /*
     * Term-specific column setting before CSV row write
     *
     * @return void
     */
    protected function modifyRowBeforeExport()
    {
        // Normalize parent
        if ($this->resource->parentId == QubitTerm::ROOT_ID)
        {
            $this->setColumn('parentId', '');
        }

        // Populate "outherFormsOfName" column with equivalent terms
        $this->setColumn('otherFormsOfName', $this->getNames(QubitTerm::ALTERNATIVE_LABEL_ID));

        // Populate scope, source, and display note columns
        $this->setNoteColumnsUsingMap($this->commonNoteMap, $this->commonNoteTypeIds);

        $this->setColumn('relatedTerms', $this->getRelatedTerms());
    }

    /*
     * Set note-related columns
     *
     * @param array $map      array that maps column names to note type names
     * @param array $typeIds  array that maps type IDs to their names
     *
     * @return void
     */
    protected function setNoteColumnsUsingMap($map, $typeIds)
    {
        foreach ($map as $column => $typeName) {
            $typeId = array_search($typeName, $typeIds);

            // If note type doesn't exist, skip
            if (false === $typeId) {
                continue;
            }

            // Populate array with note content
            $noteContent = [];
            foreach ($this->resource->getNotesByType(['noteTypeId' => $typeId]) as $note) {
                $noteContent[] = $note->content;
            }

            // Set column to array of note content
            if (0 < count($noteContent)) {
                $this->setColumn($column, $noteContent);
            }
        }
    }

    /*
     * Get alternative forms of name
     *
     * @param array $typeId  type ID of alternate form of name
     *
     * @return array  list of alternate names
     */
    private function getNames($typeId)
    {
        $results = [];

        // Compile alternative names into array
        foreach ($this->resource->getOtherNames(['typeId' => $typeId]) as $name) {
            $results[] = $name->getName();
        }

        return $results;
    }

    private function getRelatedTerms()
    {
        $relations = [];

        foreach (QubitRelation::getRelationsBySubjectId($this->resource->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID]) as $item) {
            $relations[] = (string) $item->object;
        }

        return $relations;
    }
}
