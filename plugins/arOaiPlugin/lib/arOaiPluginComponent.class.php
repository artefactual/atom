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
 * Generate  listIdentifiers response of the OAI-PMH protocol for the Access to Memory (AtoM).
 *
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
abstract class arOaiPluginComponent extends sfComponent
{
    /**
     * Execute any application/business logic for this component.
     *
     * In a typical database-driven application, execute() handles application
     * logic itself and then proceeds to create a model instance. Once the model
     * instance is initialized it handles all business logic for the action.
     *
     * A model should represent an entity in your application. This could be a
     * user account, a shopping cart, or even a something as simple as a
     * single product.
     *
     * @param sfRequest $request The current sfRequest object
     *
     * @return mixed A string containing the view name associated with this action
     */
    public function execute($request)
    {
        parent::execute($request);
    }

    public function setUpdateParametersFromRequest($request)
    {
        // If limit dates are not supplied, define them as ''
        if (!isset($request->from)) {
            $this->from = '';
        } else {
            $this->from = $request->from;
        }

        if (!isset($request->until)) {
            $this->until = '';
        } else {
            $this->until = $request->until;
        }

        if (!isset($request->set)) {
            $this->set = '';
        } else {
            $this->set = $request->set;
        }

        if (!isset($request->metadataPrefix)) {
            $this->metadataPrefix = 'oai_dc';
        } else {
            $this->metadataPrefix = $request->metadataPrefix;
        }

        // If cursor not supplied, define as 0
        if (!isset($request->cursor)) {
            $this->cursor = 0;
        } else {
            $this->cursor = $request->cursor;
        }
    }

    /**
     * Get OAI-PMH results by collection and updated_at datetime range.
     *
     * @param array $options optional parameters
     */
    public function getUpdates(array $options = [])
    {
        $presetOptions = [
            'from' => QubitOai::mysqlDate($this->from),
            'until' => QubitOai::mysqlDate($this->until),
            'offset' => $this->cursor,
            'limit' => QubitSetting::getByName('resumption_token_limit')->__toString(),
        ];

        // Get set if one has been named
        if ('' != $this->set) {
            $presetOptions['set'] = QubitOai::getMatchingOaiSet($this->set);
        }

        $options = array_merge($presetOptions, $options);

        // Get the records according to the limit dates and collection
        $update = QubitInformationObject::getUpdatedRecords($options);

        $this->publishedRecords = $update['data'];
        $this->remaining = $update['remaining'];
        $this->recordsCount = count($this->publishedRecords);

        $this->resumptionToken = base64_encode(
            json_encode(
                [
                    'from' => $this->from,
                    'until' => $this->until,
                    'cursor' => $this->cursor + $options['limit'],
                    'metadataPrefix' => $this->metadataPrefix,
                    'set' => $this->set,
                ]
            )
        );
    }

    public function setRequestAttributes($request)
    {
        $this->attributes = $request->getGetParameters();
        $this->attributesKeys = array_keys($this->attributes);
        $this->requestAttributes = '';
        foreach ($this->attributesKeys as $key) {
            $this->requestAttributes .= ' '.$key.'="'.$this->attributes[$key].'"';
        }
    }

    public static function parseXmlFormatFromMetadataPrefix($metadataPrefix)
    {
        return str_replace('oai_', '', $metadataPrefix);
    }

    public static function cachedMetadataExists($resource, $metadataPrefix)
    {
        $format = self::parseXmlFormatFromMetadataPrefix($metadataPrefix);

        return file_exists(QubitInformationObjectXmlCache::resourceExportFilePath($resource, $format, true));
    }

    public static function checkDisplayCachedMetadata($resource, $metadataPrefix)
    {
        $xmlCachingEnabled = sfConfig::get('app_cache_xml_on_save', false);

        return $xmlCachingEnabled && self::cachedMetadataExists($resource, $metadataPrefix);
    }

    public static function includeCachedMetadata($resource, $metadataPrefix)
    {
        $format = self::parseXmlFormatFromMetadataPrefix($metadataPrefix);

        include QubitInformationObjectXmlCache::resourceExportFilePath($resource, $format, true);
    }
}
