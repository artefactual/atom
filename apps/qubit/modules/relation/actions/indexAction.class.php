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
 * Get JSON representation of relation.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class RelationIndexAction extends sfAction
{
    public function execute($request)
    {
        // Check user authorization
        if (!$this->getUser()->isAuthenticated()) {
            QubitAcl::forwardUnauthorized();
        }

        $this->resource = $this->getRoute()->resource;

        $value = [];

        $value['date'] = $this->resource->date;
        $value['endDate'] = Qubit::renderDate($this->resource->endDate);
        $value['startDate'] = Qubit::renderDate($this->resource->startDate);
        $value['description'] = $this->resource->description;

        if (isset($this->resource->object)) {
            $value['object'] = $this->context->routing->generate(null, [$this->resource->object]);
            $value['objectDisplay'] = strval($this->resource->object);
        }

        if (isset($this->resource->subject)) {
            $value['subject'] = $this->context->routing->generate(null, [$this->resource->subject]);
            $value['subjectDisplay'] = strval($this->resource->subject);
        }

        if (isset($this->resource->type)) {
            if (
                QubitTaxonomy::ACTOR_RELATION_TYPE_ID == $this->resource->type->taxonomyId
                && QubitTerm::ROOT_ID != $this->resource->type->parentId
            ) {
                $value['type'] = $this->context->routing->generate(null, [$this->resource->type->parent, 'module' => 'term']);
                $value['subType'] = $this->context->routing->generate(null, [$this->resource->type, 'module' => 'term']);
                $value['subTypeDisplay'] = strval($this->resource->type);

                $value['converseSubType'] = '';
                if (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($this->resource->type->id, ['typeId' => QubitTerm::CONVERSE_TERM_ID]))) {
                    $converseSubType = $converseTerms[0]->getOpposedObject($this->resource->type);
                    $value['converseSubType'] = $this->context->routing->generate(null, [$converseSubType, 'module' => 'term']);
                    $value['converseSubTypeDisplay'] = strval($converseSubType);
                }
            } else {
                $value['type'] = $this->context->routing->generate(null, [$this->resource->type, 'module' => 'term']);
            }
        }

        if (method_exists($this, 'extraQueries')) {
            $value = $this->extraQueries($value);
        }

        return $this->renderText(json_encode($value));
    }
}
