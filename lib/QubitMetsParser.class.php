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

class QubitMetsParser
{
    private $document;
    private $resource;

    public function __construct($document, $options = [])
    {
        // Load document
        $this->document = $document;

        // Get declared namespaces in the document
        $this->namespaces = $this->document->getDocNamespaces(true);

        // For backwards compatibility, add default namespaces as they
        // were declared without name in the METS file (fits still is).
        $defaultNamespaces = [
            'mets' => 'http://www.loc.gov/METS/',
            'premis' => 'info:lc/xmlns/premis-v2',
            'fits' => 'http://hul.harvard.edu/ois/xml/ns/fits/fits_output',
        ];
        foreach ($defaultNamespaces as $name => $uri) {
            // Do not overwrite the ones declared in the METS file
            if (!isset($this->namespaces[$name])) {
                $this->namespaces[$name] = $uri;
            }
        }

        // Register namespaces for XPath queries made directly over the document
        $this->registerNamespaces($this->document, ['m' => 'mets', 'p' => 'premis', 'f' => 'fits']);
    }

    public function getStructMap()
    {
        // Check first for logical structMap
        $structMap = $this->document->xpath('//m:structMap[@TYPE="logical" and @LABEL="Hierarchical"]');

        if (false !== $structMap && 0 < count($structMap)) {
            return $structMap[0];
        }

        // Then for physical
        $structMap = $this->document->xpath('//m:structMap[@TYPE="physical"]');

        if (false !== $structMap && 0 < count($structMap)) {
            return $structMap[0];
        }
    }

    public function getDipUploadMappings($structMap)
    {
        $mappings = $lodMapping = $dmdMapping = $uuidMapping = [];

        // LOD mapping (only for hierarchical DIP upload over logical structMap)
        if ('logical' == $structMap['TYPE']) {
            $this->registerNamespaces($structMap, ['m' => 'mets']);

            foreach ($structMap->xpath('.//m:div') as $item) {
                if (null === $item['TYPE']) {
                    continue;
                }

                $lodName = (string) $item['TYPE'];

                $sql = 'SELECT
                    term.id';
                $sql .= ' FROM '.QubitTerm::TABLE_NAME.' term';
                $sql .= ' JOIN '.QubitTermI18n::TABLE_NAME.' i18n
                    ON term.id = i18n.id';
                $sql .= ' WHERE i18n.name = ?
                    AND term.taxonomy_id = ?';

                if (false !== $id = QubitPdo::fetchColumn($sql, [$lodName, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID])) {
                    $lodMapping[$lodName] = $id;
                } else {
                    // If a LOD is not found for a type, the upload process is stoped
                    throw new sfException('Level of description not found: '.$lodName);
                }
            }
        }

        // FILEID to DMD mapping
        foreach ($this->document->xpath('//m:structMap[@TYPE="logical" or @TYPE="physical"]//m:div') as $item) {
            $this->registerNamespaces($item, ['m' => 'mets']);

            if (0 < count($fptr = $item->xpath('m:fptr'))) {
                $dmdId = (string) $item['DMDID'];
                $fileId = (string) $fptr[0]['FILEID'];

                if (strlen($fileId) > 0 && strlen($dmdId) > 0) {
                    $dmdMapping[$fileId] = $dmdId;
                }
            }
        }

        // FILEID to UUID mapping
        foreach ($this->document->xpath('//m:fileSec/m:fileGrp[@USE="original"]/m:file') as $file) {
            // Get premis:objectIdentifiers in amd section for each file
            if (
                isset($file['ADMID'], $file['ID'])
                && false !== $identifiers = $this->document->xpath('//m:amdSec[@ID="'.(string) $file['ADMID'].'"]//p:objectIdentifier')
            ) {
                // Find UUID type
                foreach ($identifiers as $item) {
                    $this->registerNamespaces($item, ['p' => 'premis']);

                    if (
                        count($type = $item->xpath('p:objectIdentifierType')) > 0
                        && count($value = $item->xpath('p:objectIdentifierValue')) > 0
                        && 'UUID' == (string) $type[0]
                    ) {
                        $uuidMapping[(string) $file['ID']] = (string) $value[0];
                    }
                }
            }
        }

        $mappings['lodMapping'] = $lodMapping;
        $mappings['dmdMapping'] = $dmdMapping;
        $mappings['uuidMapping'] = $uuidMapping;

        return $mappings;
    }

    public function getMainDmdSec()
    {
        $structMap = $this->document->xpath('//m:structMap[@TYPE="physical"]');
        if (0 == count($structMap)) {
            return;
        }

        $structMap = $structMap[0];
        $this->registerNamespaces($structMap, ['m' => 'mets']);
        $divs = $structMap->xpath('m:div/m:div');
        if (0 == count($divs) || !isset($divs[0]['DMDID'])) {
            return;
        }

        return $this->getDmdSec((string) $divs[0]['DMDID']);
    }

    public function getDmdSec($dmdId)
    {
        // The DMDID attribute can contain one or more DMD section ids
        // (e.g.: DMDID="dmdSec_2 dmdSec_3"). When multiple DMD sections
        // are associated with the same file/dir we'll try to return the
        // latest one created.
        $latestDmdSec = null;
        $latestDate = '';
        foreach (explode(' ', $dmdId) as $id) {
            $dmdSecs = $this->document->xpath('//m:dmdSec[@ID="'.$id.'"]');
            if (0 == count($dmdSecs)) {
                continue;
            }

            $dmdSec = $dmdSecs[0];
            $date = $dmdSec['CREATED'];
            if (!isset($latestDmdSec) || (isset($date) && $date > $latestDate)) {
                $latestDmdSec = $dmdSec;
                $latestDate = isset($date) ? $date : '';
            }
        }

        return $latestDmdSec;
    }

    /**
     * Find the original filename
     * simple_load_string() is used to make xpath queries faster.
     *
     * @param mixed $fileId
     */
    public function getOriginalFilename($fileId)
    {
        if (
            (
                false !== $file = $this->document->xpath(
                    '//m:fileSec/m:fileGrp[@USE="original"]/m:file[@ID="'.$fileId.'"]'
                )
            )
            && (null !== $admId = $file[0]['ADMID'])
            && (false !== $originalName = $this->document->xpath(
                '//m:amdSec[@ID="'.(string) $admId.'"]/m:techMD/m:mdWrap/m:xmlData/p:object/p:originalName'
            ))
        ) {
            $parts = explode('/', (string) $originalName[0]);

            return end($parts);
        }
    }

    /**
     * The <fileGrp type="original"> provides a comprehensive catalog of all of
     * the "original" files stored in the AIP, which is useful when submission
     * documents, normalized files, etc. are not relevant.
     *
     * @return SimpleXmlElement a SimpleXML collection of fileGrp files
     */
    public function getFilesFromOriginalFileGrp()
    {
        return $this->document->xpath(
            '//m:mets/m:fileSec/m:fileGrp[@USE="original"]/m:file'
        );
    }

    /**
     * Return a simple count of original files in the AIP.
     *
     * @return int the number of original files in the AIP
     */
    public function getOriginalFileCount()
    {
        return count($this->getFilesFromOriginalFileGrp());
    }

    // AIP functions

    public function getAipSizeOnDisk()
    {
        $totalSize = 0;

        foreach ($this->document->xpath('//m:amdSec/m:techMD/m:mdWrap[@MDTYPE="PREMIS:OBJECT"]/m:xmlData') as $xmlData) {
            $this->registerNamespaces($xmlData, ['p' => 'premis']);

            if (0 < count($size = $xmlData->xpath('p:object/p:objectCharacteristics/p:size'))) {
                $totalSize += $size[0];
            }
        }

        return $totalSize;
    }

    public function getAipCreationDate()
    {
        $metsHdr = $this->document->xpath('//m:metsHdr');

        if (isset($metsHdr) && null !== $createdAt = $metsHdr[0]['CREATEDATE']) {
            return $createdAt;
        }
    }

    // Information object functions

    public function processDmdSec($xml, $informationObject)
    {
        $this->registerNamespaces($xml, ['m' => 'mets']);

        // Use the local name to accept no namespace and dc or dcterms namespaces
        $dublincore = $xml->xpath('.//m:mdWrap/m:xmlData/*[local-name()="dublincore"]/*');

        $creation = [];

        foreach ($dublincore as $item) {
            $value = trim($item->__toString());
            if (0 == strlen($value)) {
                continue;
            }

            // Strip namespaces from element names
            switch (str_replace(['dcterms:', 'dc:'], '', $item->getName())) {
                case 'title':
                    $informationObject->setTitle($value);

                    break;

                case 'creator':
                    $creation['actorName'] = $value;

                    break;

                case 'provenance':
                    $informationObject->acquisition = $value;

                    break;

                case 'coverage':
                    $informationObject->setAccessPointByName($value, ['type_id' => QubitTaxonomy::PLACE_ID]);

                    break;

                case 'subject':
                    $informationObject->setAccessPointByName($value, ['type_id' => QubitTaxonomy::SUBJECT_ID]);

                    break;

                case 'description':
                    $informationObject->scopeAndContent = $value;

                    break;

                case 'publisher':
                    $informationObject->setActorByName($value, ['event_type_id' => QubitTerm::PUBLICATION_ID]);

                    break;

                case 'contributor':
                    $informationObject->setActorByName($value, ['event_type_id' => QubitTerm::CONTRIBUTION_ID]);

                    break;

                case 'date':
                    $creation['date'] = $value;

                    break;

                case 'type':
                    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DC_TYPE_ID) as $item) {
                        if (strtolower($value) == strtolower($item->__toString())) {
                            $relation = new QubitObjectTermRelation();
                            $relation->term = $item;

                            $informationObject->objectTermRelationsRelatedByobjectId[] = $relation;

                            break;
                        }
                    }

                    break;

                case 'extent':
                case 'format':
                    $informationObject->extentAndMedium = $value;

                    break;

                case 'identifier':
                    $informationObject->identifier = $value;

                    break;

                case 'source':
                    $informationObject->locationOfOriginals = $value;

                    break;

                case 'language':
                    // TODO: the user could write "English" instead of "en"? (see symfony...widget/i18n/*)
                    $informationObject->language = [$value];

                    break;

                case 'isPartOf':
                    // TODO: ?

                    break;

                case 'rights':
                    $informationObject->accessConditions = $value;

                    break;
            }
        }

        if (count($creation) > 0) {
            $event = new QubitEvent();
            $event->typeId = QubitTerm::CREATION_ID;

            if ($creation['actorName']) {
                if (null === $actor = QubitActor::getByAuthorizedFormOfName($creation['actorName'])) {
                    $actor = new QubitActor();
                    $actor->parentId = QubitActor::ROOT_ID;
                    $actor->setAuthorizedFormOfName($creation['actorName']);
                    $actor->save();
                }

                $event->actorId = $actor->id;
            }

            if ($creation['date']) {
                // Save value without modification in free text field
                $event->date = trim($creation['date']);

                // Normalize expression of date range
                $date = str_replace(' - ', '|', $event->date);
                $dates = explode('|', $date);

                // If date is a range, set start and end dates
                if (2 == count($dates)) {
                    // Parse each component date
                    $event->startDate = Qubit::parseDate($dates[0]);
                    $event->endDate = Qubit::parseDate($dates[1]);
                }
            }

            $informationObject->eventsRelatedByobjectId[] = $event;
        }

        return $informationObject;
    }

    public function addMetsDataToInformationObject(&$resource, $objectUuid)
    {
        // Obtain amdSec id for objectUuid
        foreach ($this->document->xpath('//m:fileSec/m:fileGrp[@USE="original"]/m:file') as $item) {
            if (false !== strrpos($item['ID'], $objectUuid)) {
                $amdSecId = $item['ADMID'];

                break;
            }
        }

        if (!isset($amdSecId)) {
            throw new sfException(
                'AMD section was not found for object UUID: '.$objectUuid
            );
        }

        $this->objectXpath = '//m:amdSec[@ID="'.$amdSecId.'"]/m:techMD/m:mdWrap[@MDTYPE="PREMIS:OBJECT"]/m:xmlData/p:object/';

        $this->resource = $resource;

        $this->loadPremisObjectData();
        $this->loadFitsAudioData();
        $this->loadFitsDocumentData();
        $this->loadFitsTextData();
        $this->loadMediainfoData();
        $this->loadFormatData();
        $this->loadEventsData($amdSecId);
        $this->loadAgentsData($amdSecId);
    }

    public function registerNamespaces($element, $namespaces)
    {
        foreach ($namespaces as $key => $name) {
            if (isset($this->namespaces[$name])) {
                $element->registerXPathNamespace($key, $this->namespaces[$name]);
            }
        }
    }

    /**
     * Return an original file path and name relative to the AIP root directory.
     *
     * The file path is parsed from a METS <fileSec><file><FLocat> element
     *
     * @param string $fileId the <file @ID> attribute value
     *
     * @return null|string the file's relative path, or null if not found
     */
    public function getOriginalPathInAip($fileId)
    {
        foreach ($this->getFilesFromOriginalFileGrp() as $file) {
            if ($file['ID'] == $fileId) {
                // Get xlink:href value, e.g.
                // <mets:FLocat xlink:href="objects/pictures/Landing_zone.jpg" ... />
                return (string) $file->children('mets', true)->FLocat->attributes(
                    'http://www.w3.org/1999/xlink'
                );
            }
        }
    }

    /**
     * Get the size of the "original" file.
     *
     * @param string $fileId METS FILEID
     *
     * @return string the size of the original file
     */
    public function getOriginalFileSize($fileId)
    {
        if (
            (null !== $file = $this->getOriginalFile($fileId))
            && (null !== $admId = $file['ADMID'])
            && (null !== $size = $this->getSizeFromAmdSec($admId))
        ) {
            return (string) $size;
        }
    }

    /**
     * Get the filename of the preservation copy of a file.
     *
     * @param string $fileId METS FILEID
     *
     * @return string the filename of the preservation copy
     */
    public function getPreservationCopyFilename($fileId)
    {
        if (
            (null !== $file = $this->getPreservationFile($fileId))
            && (null !== $admId = $file['ADMID'])
        ) {
            return $this->getOriginalFileNameFromAmdSec($admId);
        }
    }

    /**
     * Get the size of the preservation copy of a file.
     *
     * @param string $fileId METS FILEID
     *
     * @return string the size of the preservation copy
     */
    public function getPreservationCopyFileSize($fileId)
    {
        if (
            (null !== $file = $this->getPreservationFile($fileId))
            && (null !== $admId = $file['ADMID'])
            && (null !== $size = $this->getSizeFromAmdSec($admId))
        ) {
            return (string) $size;
        }
    }

    /**
     * Get the datetime of the ingestion PREMIS event of the original file.
     *
     * @param string $fileId METS FILEID
     *
     * @return string the datetime of the first ingestion event
     */
    public function getOriginalFileIngestionTime($fileId)
    {
        if (
            (null !== $file = $this->getOriginalFile($fileId))
            && (null !== $admId = $file['ADMID'])
            && (null !== $events = $this->getPremisEventsByType($admId, 'ingestion'))
        ) {
            return $this->getFirstEventDateTime($events);
        }
    }

    /**
     * Get the datetime of the creation PREMIS event of the preservation file.
     *
     * @param string $fileId METS FILEID
     *
     * @return string the datetime of the first creation event
     */
    public function getPreservationCopyCreationTime($fileId)
    {
        if (
            (null !== $file = $this->getPreservationFile($fileId))
            && (null !== $admId = $file['ADMID'])
            && (null !== $events = $this->getPremisEventsByType($admId, 'creation'))
        ) {
            return $this->getFirstEventDateTime($events);
        }
    }

    /**
     * Get the <mets:file> element from the original file group.
     *
     * @param string $fileId METS FILEID
     *
     * @return SimpleXMLElement the <mets:file> element
     */
    protected function getOriginalFile($fileId)
    {
        if (
            false !== $file = $this->document->xpath(
                sprintf('//m:fileSec/m:fileGrp[@USE="original"]/m:file[@ID="%s"]', $fileId)
            )
        ) {
            return $file[0];
        }
    }

    /**
     * Get the <mets:file> element from the preservation file group.
     *
     * @param string $fileId METS FILEID
     *
     * @return SimpleXMLElement the <mets:file> element
     */
    protected function getPreservationFile($fileId)
    {
        if (
            (null !== $originalFile = $this->getOriginalFile($fileId))
            && (null !== $groupId = $originalFile['GROUPID'])
            && (false !== $file = $this->document->xpath(
                sprintf('//m:fileSec/m:fileGrp[@USE="preservation"]/m:file[@GROUPID="%s"]', $groupId)
            ))
        ) {
            return $file[0];
        }
    }

    /**
     * Get the filename part of the path in the <premis:originalName> element of a <mets:amdSec> element.
     *
     * @param string $admId METS ADMID
     *
     * @return string the filename part of the <premis:originalName> path
     */
    protected function getOriginalFileNameFromAmdSec($admId)
    {
        if (
            false !== $originalName = $this->document->xpath(
                sprintf('//m:amdSec[@ID="%s"]/m:techMD/m:mdWrap/m:xmlData/p:object/p:originalName', $admId)
            )
        ) {
            $parts = explode('/', (string) $originalName[0]);

            return end($parts);
        }
    }

    /**
     * Get the <premis:size> element of the PREMIS object in a <mets:amdSec> element.
     *
     * @param string $admId METS ADMID
     *
     * @return SimpleXMLElement the <premis:size> element
     */
    protected function getSizeFromAmdSec($admId)
    {
        if (
            false !== $size = $this->document->xpath(
                sprintf('//m:amdSec[@ID="%s"]/m:techMD/m:mdWrap/m:xmlData/p:object/p:objectCharacteristics/p:size', $admId)
            )
        ) {
            return $size[0];
        }
    }

    /**
     * Get the PREMIS events of a single type in a <mets:amdSec> element.
     *
     * @param string $admId     METS ADMID
     * @param string $eventType a PREMIS event type
     *
     * @return array the list of <premis:event> elements
     */
    protected function getPremisEventsByType($admId, $eventType)
    {
        $events = [];
        $selector = sprintf(
            '//m:amdSec[@ID="%s"]/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:EVENT"]/m:xmlData/p:event',
            $admId
        );
        foreach ($this->document->xpath($selector) as $event) {
            $this->registerNamespaces($event, ['p' => 'premis']);
            $types = $event->xpath('p:eventType');
            foreach ($types as $type) {
                if ($eventType === (string) $type) {
                    array_push($events, $event);
                }
            }
        }

        return $events;
    }

    /**
     * Get the datetime of the first event with a <premis:eventDateTime> element.
     *
     * @param array $events list of SimpleXmlElement objects representing PREMIS events
     *
     * @return string the datetime of the first event with a <premis:eventDateTime> element
     */
    protected function getFirstEventDateTime($events)
    {
        foreach ($events as $event) {
            $eventDateTimes = $event->xpath('p:eventDateTime');
            if (false !== $eventDateTimes) {
                return (string) $eventDateTimes[0];
            }
        }
    }

    private function loadPremisObjectData()
    {
        $premisObject = new QubitPremisObject();

        $fields = [
            'filename' => [
                'xpath' => $this->objectXpath.'p:originalName',
                'type' => 'lastPartOfPath',
            ],
            'puid' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:format/p:formatRegistry[p:formatRegistryName="PRONOM"]/p:formatRegistryKey',
                'type' => 'string',
            ],
            'lastModified' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/f:fits/f:toolOutput/f:tool/repInfo/lastModified',
                'type' => 'date',
            ],
            'size' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:size',
                'type' => 'string',
            ],
            'mimeType' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/f:fits/f:toolOutput/f:tool/fileUtilityOutput/mimetype',
                'type' => 'string',
            ],
        ];

        foreach ($fields as $fieldName => $options) {
            $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
            if (!empty($value)) {
                $premisObject->{$fieldName} = $value;
            }
        }

        $this->resource->premisObjects[] = $premisObject;
    }

    private function loadFitsAudioData()
    {
        $fitsAudio = [];
        $audioXpath = $this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/f:fits/f:metadata/f:audio/';

        $fields = [
            'bitDepth' => [
                'xpath' => $audioXpath.'f:bitDepth',
                'type' => 'string',
            ],
            'sampleRate' => [
                'xpath' => $audioXpath.'f:sampleRate',
                'type' => 'string',
            ],
            'channels' => [
                'xpath' => $audioXpath.'f:channels',
                'type' => 'string',
            ],
            'dataEncoding' => [
                'xpath' => $audioXpath.'f:audioDataEncoding',
                'type' => 'string',
            ],
            'offset' => [
                'xpath' => $audioXpath.'f:offset',
                'type' => 'string',
            ],
            'byteOrder' => [
                'xpath' => $audioXpath.'f:byteOrder',
                'type' => 'string',
            ],
        ];

        foreach ($fields as $fieldName => $options) {
            $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
            if (!empty($value)) {
                $fitsAudio[$fieldName] = $value;
            }
        }

        if (!empty($fitsAudio)) {
            QubitProperty::addUnique($this->resource->id, 'fitsAudio', serialize($fitsAudio), ['scope' => 'premisData', 'indexOnSave' => false]);
        }
    }

    private function loadFitsDocumentData()
    {
        $fitsDocument = [];
        $documentXpath = $this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/f:fits/f:metadata/f:document/';

        $fields = [
            'title' => [
                'xpath' => $documentXpath.'f:title',
                'type' => 'string',
            ],
            'author' => [
                'xpath' => $documentXpath.'f:author',
                'type' => 'string',
            ],
            'pageCount' => [
                'xpath' => $documentXpath.'f:pageCount',
                'type' => 'string',
            ],
            'wordCount' => [
                'xpath' => $documentXpath.'f:wordCount',
                'type' => 'string',
            ],
            'characterCount' => [
                'xpath' => $documentXpath.'f:characterCount',
                'type' => 'string',
            ],
            'language' => [
                'xpath' => $documentXpath.'f:language',
                'type' => 'string',
            ],
            'isProtected' => [
                'xpath' => $documentXpath.'f:isProtected',
                'type' => 'boolean',
            ],
            'isRightsManaged' => [
                'xpath' => $documentXpath.'f:isRightsManaged',
                'type' => 'boolean',
            ],
            'isTagged' => [
                'xpath' => $documentXpath.'f:isTagged',
                'type' => 'boolean',
            ],
            'hasOutline' => [
                'xpath' => $documentXpath.'f:hasOutline',
                'type' => 'boolean',
            ],
            'hasAnnotations' => [
                'xpath' => $documentXpath.'f:hasAnnotations',
                'type' => 'boolean',
            ],
            'hasForms' => [
                'xpath' => $documentXpath.'f:hasForms',
                'type' => 'boolean',
            ],
        ];

        foreach ($fields as $fieldName => $options) {
            $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
            if (!empty($value)) {
                $fitsDocument[$fieldName] = $value;
            }
        }

        if (!empty($fitsDocument)) {
            QubitProperty::addUnique($this->resource->id, 'fitsDocument', serialize($fitsDocument), ['scope' => 'premisData', 'indexOnSave' => false]);
        }
    }

    private function loadFitsTextData()
    {
        $fitsText = [];
        $textXpath = $this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/f:fits/f:metadata/f:text/';

        $fields = [
            'linebreak' => [
                'xpath' => $textXpath.'f:linebreak',
                'type' => 'string',
            ],
            'charset' => [
                'xpath' => $textXpath.'f:charset',
                'type' => 'string',
            ],
            'markupBasis' => [
                'xpath' => $textXpath.'f:markupBasis',
                'type' => 'string',
            ],
            'markupBasisVersion' => [
                'xpath' => $textXpath.'f:markupBasisVersion',
                'type' => 'string',
            ],
            'markupLanguage' => [
                'xpath' => $textXpath.'f:markupLanguage',
                'type' => 'string',
            ],
        ];

        foreach ($fields as $fieldName => $options) {
            $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
            if (!empty($value)) {
                $fitsText[$fieldName] = $value;
            }
        }

        if (!empty($fitsText)) {
            QubitProperty::addUnique($this->resource->id, 'fitsText', serialize($fitsText), ['scope' => 'premisData', 'indexOnSave' => false]);
        }
    }

    private function loadMediainfoData()
    {
        $trackFields = [
            'count' => [
                'xpath' => 'Count',
                'type' => 'integer',
            ],
            'videoFormatList' => [
                'xpath' => 'Video_Format_List',
                'type' => 'string',
            ],
            'videoFormatWithHintList' => [
                'xpath' => 'Video_Format_WithHint_List',
                'type' => 'string',
            ],
            'codecsVideo' => [
                'xpath' => 'Codecs_Video',
                'type' => 'string',
            ],
            'videoLanguageList' => [
                'xpath' => 'Video_Language_List',
                'type' => 'string',
            ],
            'audioFormatList' => [
                'xpath' => 'Audio_Format_List',
                'type' => 'string',
            ],
            'audioFormatWithHintList' => [
                'xpath' => 'Audio_Format_WithHint_List',
                'type' => 'string',
            ],
            'audioCodecs' => [
                'xpath' => 'Audio_codecs',
                'type' => 'string',
            ],
            'audioLanguageList' => [
                'xpath' => 'Audio_Language_List',
                'type' => 'string',
            ],
            'completeName' => [
                'xpath' => 'Complete_name',
                'type' => 'string',
            ],
            'fileName' => [
                'xpath' => 'File_name',
                'type' => 'string',
            ],
            'fileExtension' => [
                'xpath' => 'File_extension',
                'type' => 'string',
            ],
            'format' => [
                'xpath' => 'Format',
                'type' => 'string',
            ],
            'formatInfo' => [
                'xpath' => 'Format_Info',
                'type' => 'string',
            ],
            'formatUrl' => [
                'xpath' => 'Format_Url',
                'type' => 'string',
            ],
            'formatProfile' => [
                'xpath' => 'Format_profile',
                'type' => 'string',
            ],
            'formatSettings' => [
                'xpath' => 'Format_settings',
                'type' => 'string',
            ],
            'formatSettingsCabac' => [
                'xpath' => 'Format_settings__CABAC',
                'type' => 'string',
            ],
            'formatSettingsReFrames' => [
                'xpath' => 'Format_settings__ReFrames',
                'type' => 'string',
            ],
            'formatSettingsGop' => [
                'xpath' => 'Format_settings__GOP',
                'type' => 'string',
            ],
            'formatExtensionsUsuallyUsed' => [
                'xpath' => 'Format_Extensions_usually_used',
                'type' => 'string',
            ],
            'commercialName' => [
                'xpath' => 'Commercial_name',
                'type' => 'string',
            ],
            'internetMediaType' => [
                'xpath' => 'Internet_media_type',
                'type' => 'string',
            ],
            'codecId' => [
                'xpath' => 'Codec_ID',
                'type' => 'string',
            ],
            'codecIdInfo' => [
                'xpath' => 'Codec_ID_Info',
                'type' => 'string',
            ],
            'codecIdUrl' => [
                'xpath' => 'Codec_ID_Url',
                'type' => 'string',
            ],
            'codec' => [
                'xpath' => 'Codec',
                'type' => 'string',
            ],
            'codecFamily' => [
                'xpath' => 'Codec_Family',
                'type' => 'string',
            ],
            'codecInfo' => [
                'xpath' => 'Codec_Info',
                'type' => 'string',
            ],
            'codecUrl' => [
                'xpath' => 'Codec_Url',
                'type' => 'string',
            ],
            'codecCc' => [
                'xpath' => 'Codec_CC',
                'type' => 'string',
            ],
            'codecProfile' => [
                'xpath' => 'Codec_profile',
                'type' => 'string',
            ],
            'codecSettings' => [
                'xpath' => 'Codec_settings',
                'type' => 'string',
            ],
            'codecSettingsCabac' => [
                'xpath' => 'Codec_settings__CABAC',
                'type' => 'string',
            ],
            'codecSettingsRefFrames' => [
                'xpath' => 'Codec_Settings_RefFrames',
                'type' => 'string',
            ],
            'codecExtensionsUsuallyUsed' => [
                'xpath' => 'Codec_Extensions_usually_used',
                'type' => 'string',
            ],
            'fileSize' => [
                'xpath' => 'File_size',
                'type' => 'firstInteger',
            ],
            'duration' => [
                'xpath' => 'Duration',
                'type' => 'firstInteger',
            ],
            'bitRate' => [
                'xpath' => 'Bit_rate',
                'type' => 'firstInteger',
            ],
            'bitRateMode' => [
                'xpath' => 'Bit_rate_mode',
                'type' => 'string',
            ],
            'overallBitRate' => [
                'xpath' => 'Overall_bit_rate',
                'type' => 'firstInteger',
            ],
            'channels' => [
                'xpath' => 'Channel_s_',
                'type' => 'firstInteger',
            ],
            'channelPositions' => [
                'xpath' => 'Channel_positions',
                'type' => 'string',
            ],
            'samplingRate' => [
                'xpath' => 'Sampling_rate',
                'type' => 'firstInteger',
            ],
            'samplesCount' => [
                'xpath' => 'Samples_count',
                'type' => 'firstInteger',
            ],
            'compressionMode' => [
                'xpath' => 'Compression_mode',
                'type' => 'string',
            ],
            'width' => [
                'xpath' => 'Width',
                'type' => 'firstInteger',
            ],
            'height' => [
                'xpath' => 'Height',
                'type' => 'firstInteger',
            ],
            'pixelAspectRatio' => [
                'xpath' => 'Pixel_aspect_ratio',
                'type' => 'firstFloat',
            ],
            'displayAspectRatio' => [
                'xpath' => 'Display_aspect_ratio',
                'type' => 'firstStringWithTwoPoints',
            ],
            'rotation' => [
                'xpath' => 'Rotation',
                'type' => 'firstFloat',
            ],
            'frameRateMode' => [
                'xpath' => 'Frame_rate_mode',
                'type' => 'string',
            ],
            'frameRate' => [
                'xpath' => 'Frame_rate',
                'type' => 'firstFloat',
            ],
            'frameCount' => [
                'xpath' => 'Frame_count',
                'type' => 'firstInteger',
            ],
            'resolution' => [
                'xpath' => 'Resolution',
                'type' => 'firstInteger',
            ],
            'colorimetry' => [
                'xpath' => 'Colorimetry',
                'type' => 'string',
            ],
            'colorSpace' => [
                'xpath' => 'Color_space',
                'type' => 'string',
            ],
            'chromaSubsampling' => [
                'xpath' => 'Chroma_subsampling',
                'type' => 'string',
            ],
            'bitDepth' => [
                'xpath' => 'Bit_depth',
                'type' => 'firstInteger',
            ],
            'scanType' => [
                'xpath' => 'Scan_type',
                'type' => 'string',
            ],
            'interlacement' => [
                'xpath' => 'Interlacement',
                'type' => 'string',
            ],
            'bitsPixelFrame' => [
                'xpath' => 'Bits__Pixel_Frame_',
                'type' => 'firstFloat',
            ],
            'streamSize' => [
                'xpath' => 'Stream_size',
                'type' => 'firstInteger',
            ],
            'proportionOfThisStream' => [
                'xpath' => 'Proportion_of_this_stream',
                'type' => 'firstFloat',
            ],
            'headerSize' => [
                'xpath' => 'HeaderSize',
                'type' => 'firstInteger',
            ],
            'dataSize' => [
                'xpath' => 'DataSize',
                'type' => 'firstInteger',
            ],
            'footerSize' => [
                'xpath' => 'FooterSize',
                'type' => 'firstInteger',
            ],
            'language' => [
                'xpath' => 'Language',
                'type' => 'string',
            ],
            'colorPrimaries' => [
                'xpath' => 'Color_primaries',
                'type' => 'string',
            ],
            'transferCharacteristics' => [
                'xpath' => 'Transfer_characteristics',
                'type' => 'string',
            ],
            'matrixCoefficients' => [
                'xpath' => 'Matrix_coefficients',
                'type' => 'string',
            ],
            'isStreamable' => [
                'xpath' => 'IsStreamable',
                'type' => 'boolean',
            ],
            'writingApplication' => [
                'xpath' => 'Writing_application',
                'type' => 'string',
            ],
            'fileLastModificationDate' => [
                'xpath' => 'File_last_modification_date',
                'type' => 'date',
            ],
            'fileLastModificationDateLocal' => [
                'xpath' => 'File_last_modification_date__local_',
                'type' => 'date',
            ],
        ];

        // Get all tracks
        $mediainfoTracks = $this->document->xpath($this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/Mediainfo/File/track');
        $oldMets = false;

        // Check xpath query for old Archivematica METS files if no tracks were found
        if (1 > count($mediainfoTracks)) {
            $mediainfoTracks = $this->document->xpath($this->objectXpath.'p:objectCharacteristics/p:objectCharacteristicsExtension/p:Mediainfo/p:File/p:track');
            $oldMets = true;
        }

        foreach ($mediainfoTracks as $track) {
            $this->registerNamespaces($track, ['p' => 'premis']);

            $esTrack = [];

            // Load track data
            foreach ($trackFields as $fieldName => $options) {
                // Add namespace to xpath query for old METS
                if ($oldMets) {
                    $options['xpath'] = 'p:'.$options['xpath'];
                }

                $value = $this->getFieldValue($track, $options['xpath'], $options['type']);
                if (!empty($value)) {
                    $esTrack[$fieldName] = $value;
                }
            }

            if (!empty($esTrack)) {
                // Add track by type
                $type = $track->xpath('@type');

                switch ($type[0]) {
                    case 'General':
                        QubitProperty::addUnique($this->resource->id, 'mediainfoGeneralTrack', serialize($esTrack), ['scope' => 'premisData', 'indexOnSave' => false]);

                        break;

                    case 'Video':
                        QubitProperty::addUnique($this->resource->id, 'mediainfoVideoTrack', serialize($esTrack), ['scope' => 'premisData', 'indexOnSave' => false]);

                        break;

                    case 'Audio':
                        QubitProperty::addUnique($this->resource->id, 'mediainfoAudioTrack', serialize($esTrack), ['scope' => 'premisData', 'indexOnSave' => false]);

                        break;
                }
            }
        }
    }

    private function loadFormatData()
    {
        $format = [];

        $fields = [
            'formatName' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:format/p:formatDesignation/p:formatName',
                'type' => 'string',
            ],
            'formatVersion' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:format/p:formatDesignation/p:formatVersion',
                'type' => 'string',
            ],
            'formatRegistryName' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:format/p:formatRegistry/p:formatRegistryName',
                'type' => 'string',
            ],
            'formatRegistryKey' => [
                'xpath' => $this->objectXpath.'p:objectCharacteristics/p:format/p:formatRegistry/p:formatRegistryKey',
                'type' => 'string',
            ],
        ];

        foreach ($fields as $fieldName => $options) {
            // Allow empty values in format data
            QubitProperty::addUnique(
                $this->resource->id,
                $fieldName,
                $this->getFieldValue($this->document, $options['xpath'], $options['type']),
                ['scope' => 'premisData', 'indexOnSave' => false]
            );
        }
    }

    private function loadEventsData($amdSecId)
    {
        $eventFields = [
            'type' => [
                'xpath' => 'p:eventType',
                'type' => 'string',
            ],
            'dateTime' => [
                'xpath' => 'p:eventDateTime',
                'type' => 'date',
            ],
            'detail' => [
                'xpath' => 'p:eventDetail',
                'type' => 'string',
            ],
            'outcome' => [
                'xpath' => 'p:eventOutcomeInformation/p:eventOutcome',
                'type' => 'string',
            ],
            'outcomeDetailNote' => [
                'xpath' => 'p:eventOutcomeInformation/p:eventOutcomeDetail/p:eventOutcomeDetailNote',
                'type' => 'string',
            ],
        ];

        $linkingAgentIdentifierFields = [
            'type' => [
                'xpath' => 'p:linkingAgentIdentifierType',
                'type' => 'string',
            ],
            'value' => [
                'xpath' => 'p:linkingAgentIdentifierValue',
                'type' => 'string',
            ],
        ];

        // Get all events
        foreach ($this->document->xpath('//m:amdSec[@ID="'.$amdSecId.'"]/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:EVENT"]/m:xmlData/p:event') as $item) {
            $this->registerNamespaces($item, ['p' => 'premis']);

            $event = [];

            // Load event data
            foreach ($eventFields as $fieldName => $options) {
                $value = $this->getFieldValue($item, $options['xpath'], $options['type']);
                if (!empty($value)) {
                    $event[$fieldName] = $value;
                }
            }

            // Get all event linking agent identifiers
            foreach ($item->xpath('p:linkingAgentIdentifier') as $linkingAgent) {
                $this->registerNamespaces($linkingAgent, ['p' => 'premis']);

                $linkingAgentIdentifier = [];

                // Load linking agent identifier data
                foreach ($linkingAgentIdentifierFields as $fieldName => $options) {
                    $value = $this->getFieldValue($linkingAgent, $options['xpath'], $options['type']);
                    if (!empty($value)) {
                        $linkingAgentIdentifier[$fieldName] = $value;
                    }
                }

                // Add linking agent identifier to event
                $event['linkingAgentIdentifier'][] = $linkingAgentIdentifier;
            }

            // Add event dateTime to IO's dateIngested field if it's the ingestion event
            if (isset($event['type'], $event['dateTime']) && 'ingestion' == $event['type']) {
                $this->resource->premisObjects[0]->dateIngested = $event['dateTime'];
            }

            if (!empty($event)) {
                // Format identification event is stored apart
                if (isset($event['type']) && 'format identification' == $event['type']) {
                    QubitProperty::addUnique($this->resource->id, 'formatIdentificationEvent', serialize($event), ['scope' => 'premisData', 'indexOnSave' => false]);
                } else {
                    QubitProperty::addUnique($this->resource->id, 'otherEvent', serialize($event), ['scope' => 'premisData', 'indexOnSave' => false]);
                }
            }
        }
    }

    private function loadAgentsData($amdSecId)
    {
        $agentFields = [
            'identifierType' => [
                'xpath' => 'm:agentIdentifier/m:agentIdentifierType',
                'type' => 'string',
            ],
            'identifierValue' => [
                'xpath' => 'm:agentIdentifier/m:agentIdentifierValue',
                'type' => 'string',
            ],
            'name' => [
                'xpath' => 'm:agentName',
                'type' => 'string',
            ],
            'type' => [
                'xpath' => 'm:agentType',
                'type' => 'string',
            ],
        ];

        foreach ($this->document->xpath('//m:amdSec[@ID="'.$amdSecId.'"]/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:AGENT"]/m:xmlData/m:agent') as $item) {
            $this->registerNamespaces($item, ['m' => 'mets']);

            $agent = [];

            foreach ($agentFields as $fieldName => $options) {
                $value = $this->getFieldValue($item, $options['xpath'], $options['type']);
                if (!empty($value)) {
                    $agent[$fieldName] = $value;
                }
            }

            if (!empty($agent)) {
                QubitProperty::addUnique($this->resource->id, 'agent', serialize($agent), ['scope' => 'premisData', 'indexOnSave' => false]);
            }
        }
    }

    private function getFieldValue($element, $xpath, $type)
    {
        if (1 > count($results = $element->xpath($xpath))) {
            return;
        }

        switch ($type) {
            case 'lastPartOfPath':
                $parts = explode('/', (string) $results[0]);

                return end($parts);

            case 'string':
                return (string) $results[0];

            case 'date':
                return arElasticSearchPluginUtil::convertDate((string) $results[0]);

            case 'boolean':
                return 'yes' == strtolower((string) $results[0]) ? true : false;

            case 'integer':
                return (int) $results[0];

            case 'firstInteger':
                foreach ($results as $item) {
                    if (ctype_digit((string) $item)) {
                        return (int) $item;
                    }
                }

                // no break
            case 'firstFloat':
                foreach ($results as $item) {
                    if (is_float(floatval((string) $item))) {
                        return floatval((string) $item);
                    }
                }

                // no break
            case 'firstStringWithTwoPoints':
                foreach ($results as $item) {
                    if (false !== strrpos((string) $item, ':')) {
                        return (string) $item;
                    }
                }
        }
    }
}
