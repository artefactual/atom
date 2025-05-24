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
 * Display search index status.
 */
class arSearchStatusTask extends arBaseTask
{
    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        // Displaying Elasticsearch server configuration
        $config = arElasticSearchPluginConfiguration::$config;

        $this->log('Elasticsearch server information:');
        $this->log(sprintf(' - Version: %s', QubitSearch::getInstance()->client->getVersion()));
        $this->log(sprintf(' - Host: %s', $config['server']['host']));
        $this->log(sprintf(' - Port: %s', $config['server']['port']));
        $this->log(sprintf(' - Index name: %s', $config['index']['name']));
        $this->log(null);

        // Display how many objects are indexed versus how many are available
        $this->log('Document indexing status:');

        foreach ($this->availableDocumentTypes() as $docType) {
            $docTypeDescription = sfInflector::humanize(sfInflector::underscore($docType));

            $docTypeIndexedCount = $this->objectsIndexed($docType);
            $docTypeAvailableCount = $this->objectsAvailableToIndex($docType);

            $this->log(sprintf(' - %s: %d/%d', $docTypeDescription, $docTypeIndexedCount, $docTypeAvailableCount));
        }
    }

    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
        ]);

        $this->namespace = 'search';
        $this->name = 'status';

        $this->briefDescription = 'Display search index status';
        $this->detailedDescription = <<<'EOF'
The [search:status|INFO] task displays the status of search indexing for each document type.
EOF;
    }

    private function availableDocumentTypes()
    {
        $types = array_keys(QubitSearch::getInstance()->loadMappings()->asArray());
        sort($types);

        return $types;
    }

    private function objectsIndexed($docType)
    {
        // Determine model class name from document type name
        $docTypeModelClass = 'Qubit'.ucfirst($docType);

        return QubitSearch::getInstance()->index->getIndex($docTypeModelClass)->count();
    }

    private function objectsAvailableToIndex($docType)
    {
        // Determine search model class name from document type name
        $docTypeSearchModelClass = 'arElasticSearch'.ucwords($docType);

        // Create search model instance for document type and load available data
        $docTypeInstance = new $docTypeSearchModelClass();
        $docTypeInstance->load();

        return $docTypeInstance->getCount();
    }
}
