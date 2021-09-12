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

class arUnogPreservicaPluginAltIdentifierSearchAction extends QubitApiAction
{
    protected function get($request)
    {
        // Require both parameters be set
        if (empty($request->label) || empty($request->id)) {
            $message = "This endpoint requires an 'id' and 'label' parameter to be specified.";
            throw new QubitApiBadRequestException($message);
        }

        $result = $this->fetchAltId($request->label, $request->id, 'en');

        if (null != $result) {
            return ['slug' => $result->slug];
        }

        $result = $this->fetchAltId($request->label, $request->id, 'fr');

        if (null != $result) {
            return ['slug' => $result->slug];
        }

        throw new QubitApi404Exception('Object not found');
    }

    protected function fetchAltId($label, $id, $culture)
    {
        // Assemble query
        $sql = "SELECT * FROM property p
            INNER JOIN property_i18n pi ON p.id=pi.id
            INNER JOIN slug s ON p.object_id=s.object_id
            WHERE p.scope='alternativeIdentifiers' AND pi.culture=? ";

        $params = [$culture];

        // Add label criteria, if specified
        if (!empty($label)) {
            $sql .= 'AND p.name=? ';

            $params[] = $label;
        }

        // Add ID criteria, if specified
        if (!empty($id)) {
            $sql .= 'AND pi.value=? ';

            $params[] = $id;
        }

        // Attempt to fetch result
        return QubitPdo::fetchOne($sql, $params);
    }
}
