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

class digitalObjectRegenDerivativesTask extends arBaseTask
{
    private $validTypes = [];

    public static function regenerateDerivatives(&$digitalObject, $options = [])
    {
        // Determine usage ID from type flag
        switch ($options['type']) {
            case 'reference':
                $usageId = QubitTerm::REFERENCE_ID;

                break;

            case 'thumbnail':
                $usageId = QubitTerm::THUMBNAIL_ID;

                break;

            default:
                $usageId = $digitalObject->usageId; // MASTER_ID or EXTERNAL_URI_ID or EXTERNAL_FILE_ID
        }

        // If master isn't stored in AtoM, attempt to cache external resource before deleting
        // existing derivatives (an unavailable resource will result in an exception)
        if ($digitalObject->derivativesGeneratedFromExternalMaster($digitalObject->usageId)) {
            $digitalObject->getLocalPath();
        }

        // Delete derivatives normally, if not dealing with digital objects representing PDF pages
        if (null === $digitalObject->getDisplayAsCompoundObject()) {
            // Delete existing derivatives
            $criteria = new Criteria();
            $criteria->add(QubitDigitalObject::PARENT_ID, $digitalObject->id);

            // Delete only ref or thumnail derivative if "type" flag set
            if (QubitTerm::REFERENCE_ID == $usageId || QubitTerm::THUMBNAIL_ID == $usageId) {
                $criteria->add(QubitDigitalObject::USAGE_ID, $usageId);
            }

            foreach (QubitDigitalObject::get($criteria) as $derivative) {
                $derivative->delete();
            }
        } else {
            // Find parent information object's child information objects representing PDF pages
            $criteria = new Criteria();
            $criteria->add(QubitInformationObject::PARENT_ID, $digitalObject->objectId);

            foreach (QubitInformationObject::get($criteria) as $childIo) {
                if (count($childIo->digitalObjectsRelatedByobjectId)) {
                    // Attempt to get page count property from child information object's digital object
                    $property = $childIo->digitalObjectsRelatedByobjectId[0]->getPropertyByName('page_count');

                    // If a property was found then delete the information object
                    if (null !== $property) {
                        $childIo->delete();
                    }
                }
            }
        }

        // Delete existing transcript if 'keepTranscript' option is not sent or it's false,
        // we need to keep it to avoid an error trying to save a deleted property when this
        // method is called from IO rename action
        if (!isset($options['keepTranscript']) || !$options['keepTranscript']) {
            $transcriptProperty = $digitalObject->getPropertyByName('transcript');
            $transcriptProperty->delete();
        }

        // Regenerate derivatives normally, if not dealing with digital objects representing PDF pages
        if (null === $digitalObject->getDisplayAsCompoundObject()) {
            // Generate new derivatives
            $digitalObject->createRepresentations($usageId, $conn);
        } else {
            // Re-create information objects representing PDF pages and their respective derivatives
            $digitalObject->createCompoundChildren();
        }

        if ($options['index']) {
            // Update index
            $digitalObject->save();
        }

        // Destroy out-of-scope objects
        QubitDigitalObject::clearCache();
        QubitInformationObject::clearCache();
    }

    protected function configure()
    {
        // Validate "type" options
        $this->validTypes = [
            'reference',
            'thumbnail',
        ];

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('slug', 'l', sfCommandOption::PARAMETER_OPTIONAL, 'Information object or actor slug', null),
            new sfCommandOption('type', 'd', sfCommandOption::PARAMETER_OPTIONAL, 'Derivative type ("reference" or "thumbnail")', null),
            new sfCommandOption('media-type', null, sfCommandOption::PARAMETER_OPTIONAL, 'Limit regenerating derivatives to a specific media type (e.g. "audio" or "image" or "text" or "video). "Other" is not supported', null),
            new sfCommandOption('index', 'i', sfCommandOption::PARAMETER_NONE, 'Update search index (defaults to false)', null),
            new sfCommandOption('force', 'f', sfCommandOption::PARAMETER_NONE, 'No confirmation message', null),
            new sfCommandOption('only-externals', 'o', sfCommandOption::PARAMETER_NONE, 'Only external objects', null),
            new sfCommandOption('json', 'j', sfCommandOption::PARAMETER_OPTIONAL, 'Limit regenerating derivatives to IDs in a JSON file', null),
            new sfCommandOption('skip-to', null, sfCommandOption::PARAMETER_OPTIONAL, 'Skip regenerating derivatives until a certain filename is encountered', null),
            new sfCommandOption('no-overwrite', 'n', sfCommandOption::PARAMETER_NONE, 'Don\'t overwrite existing derivatives (and no confirmation message)', null),
        ]);

        $this->namespace = 'digitalobject';
        $this->name = 'regen-derivatives';
        $this->briefDescription = 'Regenerates digital object derivative from master copy';
        $this->detailedDescription = <<<'EOF'
Regenerate digital object derivatives from master copy.
EOF;
    }

    protected function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $timer = new QubitTimer();
        $skip = true;

        $databaseManager = new sfDatabaseManager($this->configuration);
        $conn = $databaseManager->getDatabase('propel')->getConnection();

        // Validate 'type' value
        if ($options['type'] && !in_array($options['type'], $this->validTypes)) {
            // If value is not valid, show message and return error code 1
            error_log(sprintf(
                'Invalid value for "type", must be one of (%s)',
                implode(',', $this->validTypes)
            ));

            exit(1);
        }

        // Validate "media-type" value
        $validMediaTypes = [
            'audio' => QubitTerm::AUDIO_ID,
            'image' => QubitTerm::IMAGE_ID,
            'text' => QubitTerm::TEXT_ID,
            'video' => QubitTerm::VIDEO_ID,
        ];
        if ($options['media-type'] && !array_key_exists($options['media-type'], $validMediaTypes)) {
            // If value is not valid, show message and return error code 1
            error_log(sprintf(
                'Invalid value for "media-type", must be one of (%s)',
                implode(',', array_keys($validMediaTypes))
            ));

            exit(1);
        }

        if ($options['index']) {
            QubitSearch::enable();
        } else {
            QubitSearch::disable();
        }

        // Get all master digital objects
        $query = 'SELECT do.id
            FROM digital_object do JOIN object o ON do.object_id = o.id
            LEFT JOIN information_object io ON o.id=io.id';
        $whereClauses = [];

        // Limit to a resource (and descendents if an information object)
        if ($options['slug']) {
            // Attempt to fetch object data using slug
            $q2 = 'SELECT o.id, o.class_name
                FROM object o JOIN slug ON o.id = slug.object_id
                WHERE slug.slug = ?';

            $row = QubitPdo::fetchOne($q2, [$options['slug']]);

            if (false === $row) {
                throw new sfException('Invalid slug');
            }

            // Add to query WHERE clause depending on resource type
            switch ($row->class_name) {
                case 'QubitInformationObject':
                    $io = QubitInformationObject::getById($row->id);
                    array_push($whereClauses, sprintf('io.lft >= %d AND io.lft <= %d', $io->lft, $io->rgt));

                    break;

                case 'QubitActor':
                    array_push($whereClauses, sprintf('o.id = %d', $row->id));

                    break;

                default:
                    throw new sfException('Invalid slug');
            }
        }

        // Only regenerate derivatives for remote digital objects
        if ($options['only-externals']) {
            array_push($whereClauses, sprintf('do.usage_id = %d', QubitTerm::EXTERNAL_URI_ID));
        }

        // Only regenerate derivatives for digital objects of specific media type
        if ($options['media-type']) {
            array_push($whereClauses, sprintf('do.media_type_id = %d', $validMediaTypes[$options['media-type']]));
        }

        // Limit ids for regeneration by json list
        if ($options['json']) {
            $ids = json_decode(file_get_contents($options['json']));
            array_push($whereClauses, 'do.id IN ('.implode(', ', $ids).')');
        }

        if ($options['no-overwrite']) {
            $query .= ' LEFT JOIN digital_object child ON do.id = child.parent_id';
            array_push($whereClauses, 'do.parent_id IS NULL AND child.id IS NULL');
        }

        // Final confirmation (skip if no-overwrite)
        if (!$options['force'] && !$options['no-overwrite']) {
            $confirm = [];

            $changed = $options['media-type'] ? $options['media-type'] : 'ALL';

            if ($options['slug']) {
                $confirm[] = 'Continuing will regenerate the derivatives for '.$changed.' digital objects (and';
                $confirm[] = 'descendants of, if an information object)';
                $confirm[] = '"'.$options['slug'].'"';
            } else {
                $confirm[] = 'Continuing will regenerate the derivatives for '.$changed.' digital objects';
            }

            $confirm[] = 'This will PERMANENTLY DELETE existing derivatives you chose to regenerate';
            $confirm[] = '';
            $confirm[] = 'Continue? (y/N)';

            if (!$this->askConfirmation($confirm, 'QUESTION_LARGE', false)) {
                $this->logSection('digital object', 'Bye!');

                return 1;
            }
        }

        // Add WHERE clauses to SQL query
        if (count($whereClauses)) {
            $query .= sprintf(' WHERE %s', implode(' AND ', $whereClauses));
        }

        $query .= ' AND do.usage_id != '.QubitTerm::OFFLINE_ID;

        // Do work
        foreach (QubitPdo::fetchAll($query) as $item) {
            $do = QubitDigitalObject::getById($item->id);

            if (null == $do) {
                continue;
            }

            if ($options['skip-to']) {
                if ($do->name != $options['skip-to'] && $skip) {
                    $this->logSection('digital object', 'Skipping '.$do->name);

                    continue;
                }

                $skip = false;
            }

            $this->logSection('digital object', sprintf(
                'Regenerating derivatives for %s... (%ss)',
                $do->name,
                $timer->elapsed()
            ));

            // Trap any exceptions when creating derivatives and continue script
            try {
                digitalObjectRegenDerivativesTask::regenerateDerivatives($do, $options);
            } catch (Exception $e) {
                // Echo error
                $this->log($e->getMessage());

                // Log error
                sfContext::getInstance()->getLogger()->err($e->getMessage());
            }
        }

        // Warn user to manually update search index
        if (!$options['index']) {
            $this->logSection('digital object', 'Please update the search index manually to reflect any changes');
        }

        $this->logSection('digital object', 'Done!');
    }
}
