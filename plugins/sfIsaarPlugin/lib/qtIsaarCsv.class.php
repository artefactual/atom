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
 * Map between ISAAR CSV format and Qubit data model.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class qtIsaarCsv extends sfIsaarPlugin
{
    public static $keymapSource;
    public static $keymapTarget = 'actor';
    public static $entityTypeLookup;
    public static $NAMES = [
        'authorizedFormOfName',
        'datesOfExistence',
        'descriptionIdentifier',
        'entityType',
        'functions',
        'generalContext',
        'history',
        'identifier',
        'institutionIdentifier',
        'internalStructures',
        'languages',
        'legalStatus',
        'maintenanceNotes',
        'mandates',
        // 'otherNames',
        // 'parallelNames',
        'places',
        'rules',
        'scripts',
        'sources',
        // 'standardizedNames'
        'uniqueId',
    ];
    protected $resource;
    protected $isaar;
    protected $sourceId;

    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->isaar = new sfIsaarPlugin($this->resource);
    }

    public function __get($name)
    {
        if (in_array($name, self::$NAMES)) {
            switch ($name) {
                case 'maintenanceNotes':
                    return $this->isaar->maintenanceNotes = $value;

                case 'uniqueId':
                    return $this->sourceId;

                default:
                    return $this->resource->__get($name);
            }
        }
    }

    public function __set($name, $value)
    {
        if (in_array($name, self::$NAMES)) {
            switch ($name) {
                case 'entityType':
                    $value = strtolower($value);
                    if (isset(self::$entityTypeLookup[$value])) {
                        $this->resource->entityTypeId = self::$entityTypeLookup[$value];
                    }

                    break;

                case 'maintenanceNotes':
                    $this->isaar->maintenanceNotes = $value;

                    break;

                case 'uniqueId':
                    $this->sourceId = $value;

                    break;

                default:
                    $this->resource->__set($name, $value);
            }
        }

        return $this;
    }

    public function save($connection = null)
    {
        $this->resource->save($connection);

        // Add to keymap table
        $keymap = new QubitKeymap();
        $keymap->sourceName = self::$keymapSource;
        $keymap->sourceId = $this->sourceId;
        $keymap->targetName = self::$keymapTarget;
        $keymap->targetId = $this->resource->id;

        $keymap->save($connection);

        return $this;
    }
}
