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

namespace AccessToMemory\test\mock;

class QubitInformationObject
{
    public $id;

    protected static $slugToIdMap = [
        'test-fonds-1' => 111111,
        'test-collection' => 222222,
        'Mixed-Case-Fonds' => 333333,
    ];

    public static function getById($id)
    {
        $obj = new self();
        $obj->id = $id;

        return $obj;
    }

    public static function getBySlug($slug)
    {
        if (array_key_exists($slug, self::$slugToIdMap)) {
            $obj = new self();
            $obj->id = self::$slugToIdMap[$slug];

            return $obj;
        }
    }

    public static function getTitle($options)
    {
        return 'Information Object';
    }

    public static function getIdentifier()
    {
        return 'IDENTIFIER';
    }

    public static function getLevelOfDescription()
    {
        return new QubitTerm();
    }

    public static function getSlug()
    {
        return 'information-object';
    }
}
