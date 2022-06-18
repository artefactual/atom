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
 * Export authority records to a CSV file.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class exportAuthorityRecordsTask extends exportBulkBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'authority-export';
    protected $briefDescription = 'Export authority record data as CSV file(s)';

    protected $detailedDescription = <<<'EOF'
Export authority record data as CSV file(s).
EOF;

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $this->checkPathIsWritable($arguments['path']);

        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
        $this->context = sfContext::createInstance($configuration);

        $itemsExported = $this->exportToCsv($arguments['path'], $options);

        $this->log('');
        $this->logSection('csv', "Export complete ({$itemsExported} authority records exported).");
    }

    /**
     * Determine the translation cultures avaiable for each actor and return
     * them as an array (with the array key being the actor ID).
     *
     * Example of the structure:
     *
     *  [
     *    '446' => ['en'],
     *    '501' => ['en', 'fr']
     *  ]
     */
    public static function getActorCultures()
    {
        $actorCultures = [];

        // Get actor translation cultures, with each actor's source culture
        // appearing first in results
        $sql = "SELECT ai.id, ai.culture
            FROM actor a
            INNER JOIN object o ON a.id=o.id
            LEFT JOIN actor_i18n ai ON a.id=ai.id
            WHERE o.class_name='QubitActor' AND a.id !=?
            ORDER BY ai.id ASC, a.source_culture=ai.culture DESC";

        $rows = QubitPdo::fetchAll($sql, [QubitActor::ROOT_ID], ['fetchMode' => PDO::FETCH_ASSOC]);

        foreach ($rows as $row) {
            $id = $row['id'];

            if (!isset($actorCultures[$id])) {
                $actorCultures[$id] = [];
            }

            $actorCultures[$id][] = $row['culture'];
        }

        return $actorCultures;
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addCoreArgumentsAndOptions();

        $this->addOptions([
            new sfCommandOption('single-slug', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export actors related to a single fonds or collection based on slug'),
            new sfCommandOption('current-level-only', null, sfCommandOption::PARAMETER_NONE, "Don't export actors related to child descriptions (when using single-slug option)", null),
        ]);
    }

    /**
     * Export actor data to CSV file(s).
     *
     * @param string $exportPath path of directory to export to
     * @param mixed  $options    export options
     *
     * @return int number of items exported
     */
    private function exportToCsv($exportPath, $options)
    {
        $itemsExported = 0;

        $actorCultures = self::getActorCultures();

        // Prepare CSV exporter
        $writer = new csvActorExport($exportPath);
        $writer->setOptions(['relations' => true]);

        // Note which actors/translations should be exported
        $actorResults = [];

        if (!empty($options['single-slug'])) {
            // Fetch information object
            $io = QubitInformationObject::getBySlug($options['single-slug']);

            if (null === $io) {
                throw new sfException('No information object found with that slug.');
            }

            // Add actors/translations, relating to parent slug, to results
            $this->addIoRelatedActorAndCulturesToResults($io, $actorCultures, $actorResults);

            // Add actors/translations, relating to descendants, to results
            if (empty($options['current-level-only'])) {
                foreach ($io->getDescendantsForExport() as $descendant) {
                    $this->addIoRelatedActorAndCulturesToResults($descendant, $actorCultures, $actorResults);
                }
            }
        } else {
            $actorResults = $actorCultures;
        }

        // Export actors and, optionally, related data
        foreach ($actorResults as $actorId => $cultures) {
            $actor = QubitActor::getById($actorId);

            foreach ($cultures as $culture) {
                $this->context->getUser()->setCulture($culture);
                $writer->exportResource($actor);
            }

            $this->indicateProgress($options['items-until-update']);

            ++$itemsExported;
        }

        return $itemsExported;
    }

    /**
     * For each actor, relating to an information object, add data about
     * available translations of it to an array received by reference.
     *
     * @param object $io            information object
     * @param array  $actorCultures array of available actors/translations
     * @param array  &$actorResults array in which to add actor/translation
     */
    private function addIoRelatedActorAndCulturesToResults($io, $actorCultures, &$actorResults)
    {
        foreach ($io->getActors() as $actor) {
            $actorResults[$actor->id] = $actorCultures[$actor->id];
        }

        foreach ($io->getNameAccessPoints() as $relation) {
            $actorResults[$relation->object->id] = $actorCultures[$relation->object->id];
        }
    }
}
