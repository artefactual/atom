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

class arUnogPreservicaPluginDigitalObjectsCreateAction extends QubitApiAction
{
    protected function post($request, $payload)
    {
        $slug = $payload->slug;
        $uuid = $payload->uuid;
        $scopeId = $payload->scopeId;

        // Fail if either a slug or UUID hasn't been provided
        if (empty($slug) || empty($uuid)) {
            $message = $this->context->i18n->__('Both slug and UUID must be specified.');

            throw new QubitApiBadRequestException($message);
        }

        // Attempt to load information object
        if (empty($io = QubitInformationObject::getBySlug($slug)) || 'QubitInformationObject' != get_class($io)) {
            $message = $this->context->i18n->__('Information object not found.');

            throw new QubitApiBadRequestException($message);
        }

        // Fail if a digital object already exists for the information object
        if (!empty($io->getDigitalObject())) {
            $message = $this->context->i18n->__('Information object already has a digital object.');

            throw new QubitApiBadRequestException($message);
        }

        // Get Preservica digital object details
        $username = sfConfig::get('app_preservica_username');
        $password = sfConfig::get('app_preservica_password');
        $host = sfConfig::get('app_preservica_host');

        $client = new arUnogPreservicaPluginRestClient($host, $username, $password);

        // Fail if credentials are invalid
        if (empty($client->token)) {
            $message = $this->context->i18n->__('Invalid token/credentials.');

            throw new QubitApiUnknownException($message);
        }

        // Fail if no object with that UUID exists
        if (empty($objectData = $client->getObjectDetails($uuid))) {
            $message = $this->context->i18n->__('Error fetching object data.');

            throw new QubitApiUnknownException($message);
        }

        $cmisId = $objectData->value->id;
        $filename = $client->getObjectDetailsPropertyByName($objectData, 'cmis:contentStreamFileName');

        // Download Preservica digital object's thumbnail to a temp file
        $thumbTempFilePath = $client->downloadThumbnailToTempDir($cmisId, $filename);

        // Create digital object with thumbnail as representation derivative
        $do = new QubitDigitalObject();
        $do->objectId = $io->id;
        $do->importFromFile($thumbTempFilePath);
        $do->save();

        // Remove thumbnail temp dir and file
        unlink($thumbTempFilePath);
        rmdir(dirname($thumbTempFilePath));

        // Store transcript, if one exists, as digital object property
        if (!empty($fullText = $client->getFullText($uuid))) {
            $this->storeTranscript($fullText, $do->id);
        }

        // Add property containing Preservica UUID
        $this->addAlternativeIdentifier($do->id, arUnogPreservicaPluginConfiguration::PRESERVICA_UUID_PROPERTY_NAME, $uuid);

        // Optionally add property containing Scope ID
        if (!empty($scopeId)) {
            $this->addAlternativeIdentifier($io->id, 'Scope ID', $scopeId);
        }

        $this->response->setStatusCode(201);

        // Update information object's Elasticsearch document
        QubitSearch::getInstance()->update($io, ['updateDescendants' => true]);

        return ['id' => (int) $do->id, 'slug' => $do->slug];
    }

    private function addAlternativeIdentifier($objectId, $label, $id)
    {
        $property = new QubitProperty();
        $property->objectId = $objectId;
        $property->name = $label;
        $property->scope = 'alternativeIdentifiers';

        $property->setValue($id, ['sourceCulture' => true]);
        $property->save();
    }

    private function storeTranscript($text, $doId)
    {
        // NOTE:
        //
        // This logic is replicated from the QubitDigitalObject model.
        // If we add this functionality to upstream AtoM we should create
        // a static method in the QubitDigitalObject model and use that.

        // Truncate PDF text to <64KB to fit in `property.value` column
        $text = mb_strcut($text, 0, 65535);

        // Update or create 'transcript' property
        $criteria = new Criteria();
        $criteria->add(QubitProperty::OBJECT_ID, $doId);

        $criteria->add(QubitProperty::NAME, 'transcript');
        $criteria->add(QubitProperty::SCOPE, 'Text extracted from source PDF file\'s text layer using pdftotext');

        if (null === $property = QubitProperty::getOne($criteria)) {
            $property = new QubitProperty();
            $property->objectId = $doId;
            $property->name = 'transcript';
            $property->scope = 'Text extracted from source PDF file\'s text layer using pdftotext';
        }

        $property->value = $text;
        $property->indexOnSave = false;

        $property->save($connection);
    }
}
