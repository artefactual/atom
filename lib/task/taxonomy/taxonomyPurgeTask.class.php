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
 * Purge taxonomy of terms.
 *
 * @author  Mike Cantelon <mike@artefactual.com>
 */
class taxonomyPurgeTask extends arBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        // Determine ID of taxonomy to purge
        $taxonomyId = $this->determineTaxonomyId($options);

        // Prevent purging of taxonomies that aren't editable
        if (in_array($taxonomyId, QubitTaxonomy::$lockedTaxonomies)) {
            throw new sfException('Taxonomy is not editable.');
        }

        // Fetch terms in taxonomy
        $criteria = new Criteria();
        $criteria->add(QubitTerm::TAXONOMY_ID, $taxonomyId);

        // Delete terms, maintaining count of deleted terms
        $termCount = 0;
        foreach (QubitTerm::get($criteria) as $term) {
            $this->log(
                sprintf('Deleting "%s" (ID %d)...', $term->getName(['sourceCulture' => true]), $term->id)
            );

            $term->delete();

            ++$termCount;
        }

        $this->log(sprintf('Done: deleted %d terms.', $termCount));
    }

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                'qubit'
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),

            new sfCommandOption(
                'taxonomy-id',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'ID of taxonomy'
            ),
            new sfCommandOption(
                'taxonomy-name',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Name of taxonomy'
            ),
            new sfCommandOption(
                'taxonomy-name-culture',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Culture to use for taxonomy name lookup'
            ),
        ]);

        $this->namespace = 'taxonomy';
        $this->name = 'purge';
        $this->briefDescription = 'Purge taxonomy.';
        $this->detailedDescription = <<<'EOF'
Purge taxonomiy of terms.
EOF;
    }

    private function determineTaxonomyId($options)
    {
        if (ctype_digit($options['taxonomy-id'])) {
            $criteria = new Criteria();
            $criteria->add(QubitTaxonomy::ID, $options['taxonomy-id']);

            if (null === QubitTaxonomy::getOne($criteria)) {
                throw new sfException('Invalid taxonomy-id.');
            }

            return $options['taxonomy-id'];
        }
        if (isset($options['taxonomy-name'])) {
            $culture = (isset($options['taxonomy-name-culture'])) ? $options['taxonomy-name-culture'] : 'en';

            $criteria = new Criteria();

            $criteria->add(QubitTaxonomyI18n::NAME, $options['taxonomy-name']);
            $criteria->add(QubitTaxonomyI18n::CULTURE, $culture);

            if (null === $taxonomy = QubitTaxonomyI18n::getOne($criteria)) {
                throw new sfException('Invalid taxonomy-name and/or taxonomy-name-culture.');
            }

            return $taxonomy->id;
        }

        throw new sfException('Either the taxonomy-id or taxonomy-name must be used to specifiy a taxonomy.');
    }
}
