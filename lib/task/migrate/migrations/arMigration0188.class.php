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

/*
 * Add separate properties for existing serialized DO format information.
 * The names of the new properties match what is expected by QubitInformationObject.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0188
{
    public const VERSION = 188;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // This maps existing names to the ones expected by QubitInformationObject
        $propertyNamesMap = [
            'name' => 'formatName',
            'version' => 'formatVersion',
            'registryName' => 'formatRegistryName',
            'registryKey' => 'formatRegistryKey',
        ];

        // Criteria for getting all the QubitProperty objects with format metadata
        $propertyName = 'format';
        $propertyScope = 'premisData';
        $criteria = new Criteria();
        $criteria->add(QubitProperty::NAME, $propertyName);
        $criteria->add(QubitProperty::SCOPE, $propertyScope);

        // Traverse all the existing QubitProperty objects and:
        //   1. Unserialize their value
        //   2. Create new properties using the updated names
        //   3. Delete the existing property
        foreach (QubitProperty::get($criteria) as $property) {
            if (null !== $value = $property->getValue(['sourceCulture' => true])) {
                $data = unserialize($value);
                if (is_array($data)) {
                    $objectId = $property->getObject()->id;
                    foreach ($propertyNamesMap as $oldName => $newName) {
                        if (array_key_exists($oldName, $data)) {
                            QubitProperty::addUnique(
                                $objectId,
                                $newName,
                                $data[$oldName],
                                ['scope' => $propertyScope, 'indexOnSave' => false]
                            );
                        }
                    }
                }
            }

            // Always delete the existing property
            $property->indexOnDelete = false;
            $property->delete();
        }

        return true;
    }
}
