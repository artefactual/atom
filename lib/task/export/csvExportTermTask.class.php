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
 * Bulk export term data as CSV.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class csvExportTermTask extends exportBulkBaseTask
{
    protected $namespace = 'csv';
    protected $name = 'term-export';
    protected $briefDescription = 'Export term data as CSV file(s)';

    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        if (isset($options['items-until-update']) && !ctype_digit($options['items-until-update'])) {
            throw new UnexpectedValueException('items-until-update must be a number');
        }

        $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
        $this->context = sfContext::createInstance($configuration);
        $conn = $this->getDatabaseConnection();

        $this->exportFileReplacePrompt($arguments['path']);

        $itemsExported = $this->exportToCsv($arguments['path'], $options);

        if ($itemsExported) {
            $this->log(sprintf("\nExport complete (%d terms exported).", $itemsExported));
        } else {
            $this->log('No terms found to export.');
        }
    }

    /**
     * Determine the translation cultures avaiable for each term and return
     * them as an array (with the array key being the term ID).
     *
     * Example of the structure:
     *
     *  [
     *    '446' => ['en'],
     *    '501' => ['en', 'fr']
     *  ]
     */
    public static function getTermCultures()
    {
        $termCultures = [];

        // Get term translation cultures, with each tern's source culture
        // appearing first in results
        $sql = 'SELECT ti.id, ti.culture
            FROM term t
            LEFT JOIN term_i18n ti ON t.id=ti.id
            WHERE t.id !=?
            ORDER BY ti.id ASC, t.source_culture=ti.culture DESC';

        $rows = QubitPdo::fetchAll($sql, [QubitTerm::ROOT_ID], ['fetchMode' => PDO::FETCH_ASSOC]);

        foreach ($rows as $row) {
            $id = $row['id'];

            if (!isset($termCultures[$id])) {
                $termCultures[$id] = [];
            }

            $termCultures[$id][] = $row['culture'];
        }

        return $termCultures;
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addCoreArgumentsAndOptions();

        $this->addOptions([
            new sfCommandOption('taxonomy-id', null, sfCommandOption::PARAMETER_OPTIONAL, 'ID of taxonomy'),
            new sfCommandOption('taxonomy-name', null, sfCommandOption::PARAMETER_OPTIONAL, 'Name of taxonomy'),
            new sfCommandOption('taxonomy-name-culture', null, sfCommandOption::PARAMETER_OPTIONAL, 'Culture to use for taxonomy name lookup'),
            new sfCommandOption('single-slug', null, sfCommandOption::PARAMETER_OPTIONAL, 'Export terms related to a single fonds or collection based on slug'),
            new sfCommandOption('current-level-only', null, sfCommandOption::PARAMETER_NONE, "Don't export terms related to child descriptions (when using single-slug option)", null),
        ]);
    }

    /**
     * Handle export of term data to CSV file(s).
     *
     * @param string $exportPath path to export to (file or directory)
     * @param mixed  $options    export options
     *
     * @return int number of items items exported
     */
    private function exportToCsv($exportPath, $options)
    {
        $termCultures = self::getTermCultures();

        // For noting which terms/translations should be exported
        $termResults = [];

        // Export either a single taxonomy or all terms
        $taxonomyId = $this->determineTaxonomyIdIfApplicable($options);

        // Handle option to export a single hierarchy or description's terms
        // only
        if (!empty($slug = $options['single-slug'])) {
            // Determine IDS of terms in hierarchy or for a single description
            $termIds = $this->getTermIdsInHierarchy(
                $slug,
                $taxonomyId,
                empty($options['current-level-only'])
            );

            // Note which terms/translations should be exported
            if (count($termIds)) {
                foreach ($termIds as $termId) {
                    $termResults[$termId] = $termCultures[$termId];
                }
            }

            // Write export and return number of term translations exported
            return $this->writeExport($exportPath, $termResults, $options);
        }

        // Export all terms or every term in a taxonomy
        if (!empty($taxonomyId)) {
            $sql = 'SELECT id FROM term WHERE taxonomy_id = ?';
            $result = QubitPdo::prepareAndExecute($sql, [$taxonomyId]);

            if ($result->rowCount()) {
                while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
                    $id = $row['id'];

                    $termResults[$id] = $termCultures[$id];
                }
            }
        } else {
            $termResults = $termCultures;
        }

        // Write export and return number of term translations exported
        return $this->writeExport($exportPath, $termResults, $options);
    }

    /**
     * Write export of term data to CSV file(s).
     *
     * @param string $exportPath  path to export to (file or directory)
     * @param array  $termResults term IDs/translation cultures to export
     * @param array  $options     export options
     *
     * @return int number of items items exported
     */
    private function writeExport($exportPath, $termResults, $options)
    {
        $itemsExported = 0;

        $writer = new csvTermExport($exportPath);

        foreach ($termResults as $termId => $cultures) {
            $term = QubitTerm::getById($termId);

            foreach ($cultures as $culture) {
                $this->context->getUser()->setCulture($culture);
                $writer->exportResource($term);
            }

            $this->indicateProgress($options['items-until-update']);

            ++$itemsExported;
        }

        return $itemsExported;
    }

    /**
     * Get term IDs relating to information objects in a hierarchy.
     *
     * @param string $slug             slug of top-level description
     * @param mixed  $taxonomyId       taxonomy ID or null (all taxonomies)
     * @param bool   $checkDescendants if true, export terms related to children
     *
     * @return array array of term IDs
     */
    private function getTermIdsInHierarchy($slug, $taxonomyId, $checkDescendants = true)
    {
        $termIds = [];

        $io = QubitInformationObject::getBySlug($slug);

        if (null === $io)
        {
            throw new sfException('No information object found with that slug.');
        }

        $this->addIoRelatedTermIdsToArray($io, $termIds, $taxonomyId);

        if ($checkDescendants) {
            foreach ($io->getDescendantsForExport() as $descendant) {
                $this->addIoRelatedTermIdsToArray($descendant, $termIds, $taxonomyId);
            }
        }

        return $termIds;
    }

    /**
     * Get terms relating to an information object and add their IDs to an
     * array if they aren't already in the array.
     *
     * @param object $io         information object instance
     * @param array  $termIds    array of term IDs
     * @param int    $taxonomyId taxonomy ID or null (all taxonomies)
     */
    private function addIoRelatedTermIdsToArray($io, &$termIds, $taxonomyId)
    {
        foreach ($io->getTermRelations() as $relation) {
            if (
                (
                    empty($taxonomyId)
                    || $relation->term->taxonomyId == $taxonomyId
                )
                && !in_array($relation->termId, $termIds)
            ) {
                $termIds[] = $relation->termId;
            }
        }
    }

    /**
     * Determine taxonomy ID (if applicable) based on options.
     *
     * @param array $options options including taxonomy ID or name
     *
     * @return mixed int if a taxonomy ID was determined or null if not
     */
    private function determineTaxonomyIdIfApplicable($options)
    {
        // Handle taxonomy lookup by ID
        if (ctype_digit($options['taxonomy-id'])) {
            $criteria = new Criteria();
            $criteria->add(QubitTaxonomy::ID, $options['taxonomy-id']);

            if (null === QubitTaxonomy::getOne($criteria)) {
                throw new UnexpectedValueException('Invalid taxonomy-id.');
            }

            return $options['taxonomy-id'];
        }

        // Handle taxonomy lookup by name
        if (isset($options['taxonomy-name'])) {
            $culture = (isset($options['taxonomy-name-culture'])) ? $options['taxonomy-name-culture'] : 'en';

            $criteria = new Criteria();
            $criteria->add(QubitTaxonomyI18n::NAME, $options['taxonomy-name']);
            $criteria->add(QubitTaxonomyI18n::CULTURE, $culture);

            if (null === $taxonomy = QubitTaxonomyI18n::getOne($criteria)) {
                throw new UnexpectedValueException('Invalid taxonomy-name and/or taxonomy-name-culture.');
            }

            return $taxonomy->id;
        }
    }

    /**
     * If a file already exists at the export path then offer to replace it.
     *
     * @param string $exportPath path to export to
     */
    private function exportFileReplacePrompt($exportPath)
    {
        if (file_exists($exportPath) && is_file($exportPath)) {
            if ('y' != strtolower(readline('The export file already exists. Do you want to replace it? [y/n*] '))) {
                throw new sfException('Export file already exists: aborting.');
            }

            unlink(realpath($exportPath));
        }
    }
}
