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

class QubitKeymap
{
    public $sourceName;
    public $sourceId;
    public $targetId;
    public $targetName;

    public static function getTargetId($sourceName, $sourceId, $targetName = 'information_object')
    {
        $keymap = [
            'information_object' => [
                'test_import' => ['123' => '567', '125' => '568'],
            ],
        ];

        if (
          isset($keymap[$targetName], $keymap[$targetName][$sourceName], $keymap[$targetName][$sourceName][$sourceId])
      ) {
            return $keymap[$sourceName][$sourceId];
        }

        return false;
    }
}
