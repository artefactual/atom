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
 * Send descriptive metadata updates for changed descriptions to Preservica.
 */
class updatePreservicaMetadataTask extends arBaseTask
{
    private $updatedObjects = [];

    public function execute($arguments = [], $options = [])
    {
        parent::execute($arguments, $options);

        $username = sfConfig::get('app_preservica_username');
        $password = sfConfig::get('app_preservica_password');
        $host = sfConfig::get('app_preservica_host');

        $client = new arUnogPreservicaPluginRestClient($host, $username, $password);

        if (empty($client->token)) {
            throw new Exception('Unable to authenticate and secure a Preservica token.');
        }

        if (!isset($options['slug'])) {
            $this->updateAll($options, $client);
        } else {
            $slug = $options['slug'];
            $objectId = $this->fetchObjectId($slug);
            $preservicaId = $this->fetchPreservicaId($objectId);
            $this->update($options, $client, $objectId, $preservicaId, $slug);
        }
    }

    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('days', null, sfCommandOption::PARAMETER_OPTIONAL, 'Number of days for information objects last updated filter', null),
            new sfCommandOption('format', null, sfCommandOption::PARAMETER_OPTIONAL, 'Format to export ("ead" or "dc")', null),
            new sfCommandOption('slug', null, sfCommandOption::PARAMETER_OPTIONAL, 'Slug for single description to update in Preservica', null),
            new sfCommandOption('update-parents', 'p', sfCommandOption::PARAMETER_NONE, 'Flag indicating whether to update parent descriptions', null),
            new sfCommandOption('dry-run', 'd', sfCommandOption::PARAMETER_NONE, 'Flag indicating whether to do dry run (log records to be updated and exit)', null),
        ]);

        $this->namespace = 'tools';
        $this->name = 'update-preservica';

        $this->briefDescription = 'Send updated descriptive metadata to Preservica';
        $this->detailedDescription = <<<'EOF'
Send updated descriptive metadata to Preservica, replacing any existing descriptive XML metadata fragments.
EOF;
    }

    private function fetchPreservicaId($objectId)
    {
        $sql = 'SELECT value FROM property_i18n pi
            LEFT JOIN property p ON p.id = pi.id
            LEFT JOIN digital_object do ON do.id = p.object_id
            WHERE do.object_id = ?
            AND p.name = ?;';

        return QubitPdo::fetchColumn($sql, [
            $objectId,
            arUnogPreservicaPluginConfiguration::PRESERVICA_UUID_PROPERTY_NAME,
        ]);
    }

    private function fetchObjectId($slug)
    {
        $sql = 'SELECT object_id FROM slug WHERE slug = ?';
        return QubitPdo::fetchColumn($sql, [$slug]);
    }

    private function putUpdatedObjectDetails($client, $url, $io, $xml, $preservicaId, $resourceType, $logger)
    {
        // Get Preservica XIP-namedspaced data from Preservica object XML
        $ns = $xml->getNamespaces(true);
        $xip_ns = $ns['xip'];
        $xipData = $xml->children($xip_ns);

        $title = $io->title;
        $refCode = $io->identifier;

        // Fetch Security Tag and Parent UUID values from Preservica object XML.
        // If this information is missing or does not match the existing record,
        // updating the title and description will fail. The path to these fields
        // varies depending on whether the Preservica record is an
        // InformationObject (intellectual entity) or StructuralObject (directory)
        if ('InformationObject' === $resourceType) {
            $securityTag = $xipData->InformationObject->SecurityTag;
            $parent = $xipData->InformationObject->Parent;
        } else {
            $securityTag = $xipData->StructuralObject->SecurityTag;
            $parent = $xipData->StructuralObject->Parent;
        }

        // Update title and description in Preservica record via PUT request
        $client->putObjectDetails($url, $resourceType, $preservicaId, $title, $refCode, $securityTag, $parent);
        $httpStatus = $client->getHttpStatus();
        if (200 != $httpStatus) {
            $format = 'Error: Unable to PUT title and description for Preservica object: %s. Status code: %s';
            $logger->log(sprintf($format, $preservicaId, $httpStatus));
        }
    }

    private function deleteExistingMetadataFragments($client, $xml, $logger)
    {
        $metadataFragments = $xml->AdditionalInformation[0]->Metadata[0];
        foreach ($metadataFragments as $fragment) {
            $fragmentUrl = $fragment[0];
            $client->deletePreservicaResource($fragmentUrl);
            $httpStatus = $client->getHttpStatus();
            if (204 != $httpStatus) {
                $format = 'Error: Unable to DELETE Preservica descriptive metadata fragment with url %s. Status code: %s';
                $logger->log(sprintf($format, $fragmentUrl, $httpStatus));
            }
        }
    }

    private function postDescriptiveMetadata($client, $url, $xmlRepresentation, $preservicaId, $logger)
    {
        $client->postDescriptiveMetadata($url, $xmlRepresentation);
        $httpStatus = $client->getHttpStatus();
        if (200 != $httpStatus) {
            $format = 'Error: Unable to POST metadata for Preservica object: %s. Status code: %s';
            $logger->log(sprintf($format, $preservicaId, $httpStatus));
        }
    }

    private function updateParent($client, $io, $xml, $resourceType, $options)
    {
        // Check if there is a non-root parent to update from AtoM
        if ($io->id === $io->getCollectionRoot()->id) {
            return;
        }

        if (null === $io->parentId || QubitInformationObject::ROOT_ID === $io->parentId) {
            return;
        }

        // Get UUID of Preservica parent
        $ns = $xml->getNamespaces(true);
        $xip_ns = $ns['xip'];
        $xipData = $xml->children($xip_ns);

        if ('InformationObject' === $resourceType) {
            $preservicaParentUUID = $xipData->InformationObject->Parent;
        } else {
            $preservicaParentUUID = $xipData->StructuralObject->Parent;
        }

        if (empty($preservicaParentUUID)) {
            return;
        }

        // Sync metadata for parent if it hasn't already been updated
        if (!in_array($io->parentId, $this->updatedObjects)) {
            $options['resourceType'] = 'StructuralObject';
            $this->update($options, $client, $io->parentId, $preservicaParentUUID, $io->slug);
        }
    }

    private function update($options, $client, $objectId, $preservicaId, $slug)
    {
        if (isset($options['logger'])) {
            $logger = $options['logger'];
        } else {
            $logger = new sfCommandLogger(new sfEventDispatcher());
        }

        $format = 'Description to update: %s (AtoM Object ID: %s, Preservica ID: %s)';
        $logger->log(sprintf($format, $slug, $objectId, $preservicaId));

        // Quit without updating if this is a dry-run
        if ($options['dry-run']) {
            return;
        }

        if (null == $preservicaId) {
            $format = 'Error: No Preservica UUID found for object %s. Skipping.';
            $logger->log(sprintf($format, $objectId));

            return;
        }

        // If we have the ID for a QubitDigitalObject, find its associated
        // InformationObject and use that moving forward.
        if (null !== $do = QubitDigitalObject::getById($objectId)) {
            $objectId = $do->__get('objectId', ['clean' => true]);
        }

        if (null === $io = QubitInformationObject::getById($objectId)) {
            $format = 'Error: Unable to retrieve AtoM InformationObject %s. Skipping.';
            $logger->log(sprintf($format, $objectId));

            return;
        }

        $cacheResource = new QubitInformationObjectXmlCacheResource($io);
        $format = 'ead';
        if (isset($options['format']) && 'dc' === $options['format']) {
            $format = 'dc';
        }
        // Export description for current level only
        $xmlRepresentation = $cacheResource->generateXmlRepresentation($format, true);

        // An AtoM InformationObject may correspond to an InformationObject
        // or a StructuralObject (directory) in Preservica. Determine which is
        // correct and use the appropriate URL moving forward.
        $resourceType = 'InformationObject';
        $url = '/entity/information-objects/'.$preservicaId;

        if (isset($options['resourceType'])) {
            $resourceType = $options['resourceType'];
        }

        $objectDetails = $client->getObjectDetails($preservicaId);
        if (404 == $client->getHttpStatus() or null === $objectDetails) {
            $resourceType = 'StructuralObject';
        }

        if ('StructuralObject' === $resourceType) {
            $url = '/entity/structural-objects/'.$preservicaId;
        }

        // Get existing object XML from Preservica to use in updates
        $preservicaXml = $client->getObjectDetailsXml($url);
        if (empty($preservicaXml)) {
            $format = 'Error: Unable to fetch XML from Preservica for object with Preservica UUID %s. Skipping.';
            $logger->log(sprintf($format, $preservicaId));

            return;
        }

        $xml = simplexml_load_string($preservicaXml);

        $this->putUpdatedObjectDetails($client, $url, $io, $xml, $preservicaId, $resourceType, $logger);

        $this->deleteExistingMetadataFragments($client, $xml, $logger);

        $this->postDescriptiveMetadata($client, $url, $xmlRepresentation, $preservicaId, $logger);

        $this->updatedObjects[] = $objectId;

        if ($options['update-parents']) {
            $this->updateParent($client, $io, $xml, $resourceType, $options);
        }
    }

    private function updateAll($options, $client)
    {
        $logger = new sfCommandLogger(new sfEventDispatcher());

        // Default to last 10 years if 'days' value not passed
        $days = 365 * 10;
        if (isset($options['days'])) {
            $days = $options['days'];
        }

        // Determine updated InformationObjects
        $sql = 'SELECT o.id as objectid, s.slug as slug
            FROM object o
            LEFT JOIN slug s on o.id = s.object_id
            WHERE o.class_name = "QubitInformationObject"
            AND o.id <> '.QubitInformationObject::ROOT_ID.'
            AND o.updated_at >= now() - interval ? day
            ORDER BY o.updated_at DESC;';

        $updatedDescriptions = QubitPdo::fetchAll($sql, [$days]);

        $descriptionsCount = count($updatedDescriptions);

        if ($descriptionsCount) {
            $format = '%s descriptions to be updated in Preservica';
            $logger->log(sprintf($format, $descriptionsCount));

            // Update each Preservica record
            foreach ($updatedDescriptions as $updatedDescription) {
                $options['logger'] = $logger;
                $updatedObjectId = $updatedDescription->{'objectid'};
                $slug = $updatedDescription->{'slug'};
                $preservicaId = $this->fetchPreservicaId($updatedObjectId);
                if (!empty($preservicaId)) {
                    $this->update($options, $client, $updatedObjectId, $preservicaId, $slug);
                }
            }
        }

        $logger->log('Done.');
    }
}
