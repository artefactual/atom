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

class ApiInformationObjectsTreeAction extends QubitApiAction
{
    protected function get($request)
    {
        $io = QubitInformationObject::getBySlug($request->slug);

        // Check that user is authorized to read this description and checks
        // viewDraft authorization.
        if (!QubitAcl::check($io, 'read')) {
            throw new QubitApiNotAuthorizedException();
        }

        $result = $this->informationObjectToArray($io);
        $children = $this->getChildren($io->id);

        if (count($children)) {
            $result['children'] = $children;
        }

        return $result;
    }

    protected function getChildren($parentId)
    {
        $results = [];

        $criteria = new Criteria();
        $criteria->add(QubitInformationObject::PARENT_ID, $parentId);

        $informationObjects = QubitInformationObject::get($criteria);

        foreach ($informationObjects as $io) {
            if (!QubitAcl::check($io, 'read')) {
                continue;
            }

            $item = $this->informationObjectToArray($io);

            $children = $this->getChildren($io->id);

            if (count($children)) {
                $item['children'] = $children;
            }

            array_push($results, $item);
        }

        return $results;
    }

    protected function informationObjectToArray($io)
    {
        $ioData = [
            'title' => $io->title,
            'identifier' => $io->identifier,
            'slug' => $io->slug,
        ];

        if (null !== $io->getLevelOfDescription()) {
            $ioData['level'] = $io->getLevelOfDescription()->getName(['culture' => 'en']);
        }

        return $ioData;
    }
}
