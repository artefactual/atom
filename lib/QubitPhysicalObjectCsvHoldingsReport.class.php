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
 * Exporter for physical object holdings CSV data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitPhysicalObjectCsvHoldingsReport
{
    public static $headerRow = [
        'physicalObjectName',
        'physicalObjectLocation',
        'physicalObjectType',
        'holdingType',
        'holdingIdentifier',
        'holdingTitle',
        'levelOfDescription',
        'holdingSlug',
    ];

    public static $defaultTypeMap = [
        'description' => 'QubitInformationObject',
        'accession' => 'QubitAccession',
    ];
    protected $ormClasses;
    protected $typeMap;

    // Default options
    protected $options = [
        'suppressEmpty' => false,
        'holdingType' => null,
    ];

    public function __construct(array $options = [])
    {
        // Set options to defaults if unset and default is set
        foreach ($this->options as $name => $default) {
            if (null !== $default) {
                $options[$name] = (isset($options[$name])) ? $options[$name] : $default;
            }
        }

        $this->setTypeMap(self::$defaultTypeMap);

        $this->setOptions($options);

        $this->setOrmClasses([
            'informationobject' => QubitInformationObject::class,
            'accession' => QubitAccession::class,
            'physicalobject' => QubitPhysicalObject::class,
        ]);
    }

    public function setOptions(?array $options = null)
    {
        if (empty($options)) {
            return;
        }

        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function getOption(string $name)
    {
        switch ($name) {
            case 'suppressEmpty':
                return $this->getSuppressEmpty();

            case 'holdingType':
                return $this->getHoldingType();

            default:
                throw new UnexpectedValueException(sprintf('Invalid option "%s".', $name));
        }
    }

    public function setOption(string $name, $value)
    {
        switch ($name) {
            case 'suppressEmpty':
                $this->setSuppressEmpty($value);

                break;

            case 'holdingType':
                $this->setHoldingType($value);

                break;

            default:
                throw new UnexpectedValueException(sprintf('Invalid option "%s".', $name));
        }
    }

    public function getSuppressEmpty()
    {
        return $this->suppressEmpty;
    }

    public function setSuppressEmpty($value)
    {
        if (!is_bool($value)) {
            $message = sfContext::getInstance()->i18n->__('Suppress empty must be set to a boolean value.');

            throw new UnexpectedValueException($message);
        }

        $this->suppressEmpty = $value;
    }

    public function getHoldingType()
    {
        return $this->holdingType;
    }

    public function setHoldingType(string $value)
    {
        $value = ('none' == strtolower($value)) ? 'none' : $value;

        if (!in_array($value, $this->allowedHoldingTypes())) {
            $message = sprintf(
                sfContext::getInstance()->i18n->__('Invalid holding type "%s" (must be one of: %s).'),
                $value,
                implode(', ', $this->allowedHoldingTypes())
            );

            throw new UnexpectedValueException($message);
        }

        $this->holdingType = $value;
    }

    public function setOrmClasses(array $classes)
    {
        $this->ormClasses = $classes;
    }

    public function setTypeMap(array $typeMap)
    {
        $this->typeMap = $typeMap;
    }

    public function allowedHoldingTypes()
    {
        return array_merge(array_values($this->typeMap), ['none']);
    }

    public function write(string $filepath)
    {
        $writer = \League\Csv\Writer::createFromPath($filepath, 'w+');
        $writer->insertOne(self::$headerRow);
        $this->export($writer);
    }

    public function export(object $writer)
    {
        $sql = "SELECT p.id \r
            FROM ".$this->ormClasses['physicalobject']::TABLE_NAME." p \r
            INNER JOIN physical_object_i18n pi \r
            ON p.id=pi.id \r
            WHERE p.source_culture=pi.culture \r
            ORDER BY pi.name";

        $physObjects = QubitPdo::fetchAll($sql, [], ['fetchMode' => PDO::FETCH_COLUMN]);

        foreach ($physObjects as $id) {
            $physObject = $this->ormClasses['physicalobject']::getById($id);
            $this->exportPhysicalObjectAndHoldings($writer, $physObject);
        }
    }

    public function exportPhysicalObjectAndHoldings(object $writer, object $physObject)
    {
        $holdingsData = $this->fetchHoldings($physObject->id);
        $this->writeRowIfNecessary($writer, $physObject, $holdingsData);
    }

    public function writeRowIfNecessary(object $writer, object $physObject, array $holdingsData)
    {
        // Start row with physical object-related column values
        $row = [
            $physObject->getName(['cultureFallback' => true]),
            $physObject->getLocation(['cultureFallback' => true]),
            $physObject->getType(['cultureFallback' => true]),
        ];

        // Add single row or multiple rows depending on whether or not physical
        // object is empty
        if (empty($holdingsData['total'])) {
            if ($this->getSuppressEmpty()) {
                return;
            }

            $row = $this->addEmptyHoldingColumnsToRow($row);
            $writer->insertOne($row);
        } elseif ('none' != $this->getHoldingType()) {
            $this->writePhysicalObjectAndHoldings($writer, $row, $holdingsData);
        }
    }

    public function addEmptyHoldingColumnsToRow(array $row = [])
    {
        while (count($row) < count(self::$headerRow)) {
            $row[] = '';
        }

        return $row;
    }

    public function fetchHoldings(string $physicalObjectId)
    {
        // Fetch physical object's holdings
        $sql = "SELECT r.object_id, o.class_name \r
            FROM ".QubitRelation::TABLE_NAME." r \r
            INNER JOIN ".QubitObject::TABLE_NAME." o \r
            ON r.object_id=o.id \r
            WHERE r.subject_id=? \r
            AND r.type_id=?";

        $params = [$physicalObjectId, QubitTerm::HAS_PHYSICAL_OBJECT_ID];

        $rows = QubitPdo::fetchAll($sql, $params, ['fetchMode' => PDO::FETCH_ASSOC]);

        return $this->summarizeHoldingsData($rows);
    }

    public function summarizeHoldingsData(array $rows)
    {
        $holdingsData = ['total' => count($rows), 'types' => []];

        foreach ($rows as $row) {
            $className = $row['class_name'];

            if (empty($holdingsData['types'][$className])) {
                $holdingsData['types'][$className] = ['total' => 0, 'holdings' => []];
            }

            unset($row['class_name']); // Unset as it'd be redundant
            $holdingsData['types'][$className]['holdings'][] = $row['object_id'];
            ++$holdingsData['types'][$className]['total'];
        }

        return $holdingsData;
    }

    public function writePhysicalObjectAndHoldings(object $writer, array $row, array $holdingsData)
    {
        // If a specific holding type is selected remove data for other types
        foreach ($holdingsData['types'] as $className => $typeData) {
            if (!empty($this->getHoldingType()) && $this->getHoldingType() != $className) {
                unset($holdingsData['types'][$className]);
            }
        }

        // Add CSV rows for each holding
        foreach ($holdingsData['types'] as $className => $typeData) {
            foreach ($typeData['holdings'] as $holdingId) {
                $resource = $className::getById($holdingId);

                $holdingRow = $row;

                $levelOfDescription = '';
                if (substr_count(get_class($resource), 'InformationObject') && !empty($resource->getLevelOfDescription())) {
                    $levelOfDescription = $resource->getLevelOfDescription()->getName(['cultureFallback' => true]);
                }

                $holdingRow[] = array_search($className, $this->typeMap);
                $holdingRow[] = $resource->getIdentifier();
                $holdingRow[] = $resource->getTitle(['cultureFallback' => true]);
                $holdingRow[] = $levelOfDescription;
                $holdingRow[] = $resource->getSlug();

                $writer->insertOne($holdingRow);
            }
        }
    }
}
