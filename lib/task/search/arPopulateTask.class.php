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
 * Populate search index.
 */
class arSearchPopulateTask extends sfBaseTask
{
    public function execute($arguments = [], $options = [])
    {
        sfContext::createInstance($this->configuration);
        sfConfig::add(QubitSetting::getSettingsArray());

        // If show-types flag set, show types available to index
        if (!empty($options['show-types'])) {
            $this->log(sprintf('Available document types that can be excluded: %s', implode(', ', $this->availableDocumentTypes())));
            $this->ask('Press the Enter key to continue indexing or CTRL-C to abort...');
        }

        new sfDatabaseManager($this->configuration);

        // Index by slug, if specified, or all indexable resources except those with an excluded type
        if ($options['slug']) {
            $logMessage = (false !== $this->attemptIndexBySlug($options)) ? 'Slug indexed.' : 'Slug not found.';
            $this->log($logMessage);
        } else {
            $populateOptions = [];
            $populateOptions['excludeTypes'] = (!empty($options['exclude-types'])) ? explode(',', strtolower($options['exclude-types'])) : null;
            $populateOptions['update'] = $options['update'];

            QubitSearch::getInstance()->populate($populateOptions);
        }
    }

    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('slug', null, sfCommandOption::PARAMETER_OPTIONAL, 'Slug of resource to index (ignoring exclude-types option).'),
            new sfCommandOption('ignore-descendants', null, sfCommandOption::PARAMETER_NONE, "Don't index resource's descendants (applies to --slug option only)."),
            new sfCommandOption('exclude-types', null, sfCommandOption::PARAMETER_OPTIONAL, 'Exclude document type(s) (command-separated) from indexing'),
            new sfCommandOption('show-types', null, sfCommandOption::PARAMETER_NONE, 'Show available document type(s), that can be excluded, before indexing'),
            new sfCommandOption('update', null, sfCommandOption::PARAMETER_NONE, "Don't delete existing records before indexing."),
        ]);

        $this->namespace = 'search';
        $this->name = 'populate';

        $this->briefDescription = 'Populates the search index';
        $this->detailedDescription = <<<'EOF'
The [search:populate|INFO] task empties, populates, and optimizes the index
in the current project. It may take quite a while to run.

To exclude a document type, use the --exclude-types option. For example:

  php symfony search:populate --exclude-types="term,actor"

To see a list of available document types that can be excluded use the --show-types option.
EOF;
    }

    private function availableDocumentTypes()
    {
        $types = array_keys(QubitSearch::getInstance()->loadMappings()->asArray());
        sort($types);

        return $types;
    }

    private function attemptIndexBySlug($options)
    {
        // Abort if resource doesn't exist for the provided slug
        if (null == $resource = QubitObject::getBySlug($options['slug'])) {
            return false;
        }

        // For information objects, allow optional skipping of descendants
        if ($resource instanceof QubitInformationObject) {
            $options = ['updateDescendants' => !$options['ignore-descendants']];
            QubitSearch::getInstance()->update($resource, $options);
        } else {
            QubitSearch::getInstance()->update($resource);
        }
    }
}
