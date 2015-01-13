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

  public function __construct($filepath, $options = array())
  {
    if (!file_exists($filepath))
    {
      throw new sfException('METS XML file was not found in:' . $filepath);
    }

    // Load document
    $this->document = new SimpleXMLElement(@file_get_contents($filepath));

    if (!isset($this->document))
    {
      throw new sfException('METS XML file in \'' . $filepath . '\' could not be opened.');
    }

    // Register namespaces
    $this->document->registerXPathNamespace('m', 'http://www.loc.gov/METS/');
    $this->document->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');
    $this->document->registerXPathNamespace('f', 'http://hul.harvard.edu/ois/xml/ns/fits/fits_output');
  }

  public function getInformationObjectDataForSearchIndex($objectUuid)
  {
    // Obtain amdSec id for objectUuid
    foreach ($this->document->xpath('//m:fileSec/m:fileGrp[@USE="original"]/m:file') as $item)
    {
      if (false !== strrpos($item['ID'], $objectUuid))
      {
        $amdSecId = $item['ADMID'];

        break;
      }
    }

    if (!isset($amdSecId))
    {
      throw new sfException('AMD section was not found for object UUID: ' . $objectUuid);
    }

    $this->objectXpath = '//m:amdSec[@ID="'.$amdSecId.'"]/m:techMD/m:mdWrap[@MDTYPE="PREMIS:OBJECT"]/m:xmlData/s:object/';

    $this->ioData = array();

    $this->loadObjectData();
    $this->loadFitsAudioData();
    $this->loadFitsDocumentData();
    $this->loadFitsTextData();
    $this->loadMediainfoData();
    $this->loadFormatData();
    $this->loadEventsData($amdSecId);
    $this->loadAgentsData($amdSecId);

    if (empty($this->ioData))
    {
      return;
    }

    return $this->ioData;
  }

  private function loadObjectData()
  {
    $fields = array(
      'filename' => array(
        'xpath' => $this->objectXpath.'s:originalName',
        'type' => 'lastPartOfPath'),
      'puid' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:format/s:formatRegistry[s:formatRegistryName="PRONOM"]/s:formatRegistryKey',
        'type' => 'string'),
      'lastModified' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:toolOutput/f:tool/repInfo/lastModified',
        'type' => 'date'),
      'size' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:size',
        'type' => 'string'),
      'mimeType' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:toolOutput/f:tool/fileUtilityOutput/mimetype',
        'type' => 'string'),
      'exiftoolRawOutput' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:toolOutput/f:tool/exiftool/rawOutput',
        'type' => 'string'));

    foreach ($fields as $fieldName => $options)
    {
      $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
      if (!empty($value))
      {
        $this->ioData[$fieldName] = $value;
      }
    }
  }

  private function loadFitsAudioData()
  {
    $audioXpath = $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:metadata/f:audio/';

    $fields = array(
      'bitDepth' => array(
        'xpath' => $audioXpath.'f:bitDepth',
        'type' => 'string'),
      'sampleRate' => array(
        'xpath' => $audioXpath.'f:sampleRate',
        'type' => 'string'),
      'channels' => array(
        'xpath' => $audioXpath.'f:channels',
        'type' => 'string'),
      'dataEncoding' => array(
        'xpath' => $audioXpath.'f:audioDataEncoding',
        'type' => 'string'),
      'offset' => array(
        'xpath' => $audioXpath.'f:offset',
        'type' => 'string'),
      'byteOrder' => array(
        'xpath' => $audioXpath.'f:byteOrder',
        'type' => 'string'));

    foreach ($fields as $fieldName => $options)
    {
      $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
      if (!empty($value))
      {
        $this->ioData['audio'][$fieldName] = $value;
      }
    }
  }

  private function loadFitsDocumentData()
  {
    $documentXpath = $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:metadata/f:document/';

    $fields = array(
      'title' => array(
        'xpath' => $documentXpath.'f:title',
        'type' => 'string'),
      'author' => array(
        'xpath' => $documentXpath.'f:author',
        'type' => 'string'),
      'pageCount' => array(
        'xpath' => $documentXpath.'f:pageCount',
        'type' => 'string'),
      'wordCount' => array(
        'xpath' => $documentXpath.'f:wordCount',
        'type' => 'string'),
      'characterCount' => array(
        'xpath' => $documentXpath.'f:characterCount',
        'type' => 'string'),
      'language' => array(
        'xpath' => $documentXpath.'f:language',
        'type' => 'string'),
      'isProtected' => array(
        'xpath' => $documentXpath.'f:isProtected',
        'type' => 'boolean'),
      'isRightsManaged' => array(
        'xpath' => $documentXpath.'f:isRightsManaged',
        'type' => 'boolean'),
      'isTagged' => array(
        'xpath' => $documentXpath.'f:isTagged',
        'type' => 'boolean'),
      'hasOutline' => array(
        'xpath' => $documentXpath.'f:hasOutline',
        'type' => 'boolean'),
      'hasAnnotations' => array(
        'xpath' => $documentXpath.'f:hasAnnotations',
        'type' => 'boolean'),
      'hasForms' => array(
        'xpath' => $documentXpath.'f:hasForms',
        'type' => 'boolean'));

    foreach ($fields as $fieldName => $options)
    {
      $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
      if (!empty($value))
      {
        $this->ioData['document'][$fieldName] = $value;
      }
    }
  }

  private function loadFitsTextData()
  {
    $textXpath = $this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/f:fits/f:metadata/f:text/';

    $fields = array(
      'linebreak' => array(
        'xpath' => $textXpath.'f:linebreak',
        'type' => 'string'),
      'charset' => array(
        'xpath' => $textXpath.'f:charset',
        'type' => 'string'),
      'markupBasis' => array(
        'xpath' => $textXpath.'f:markupBasis',
        'type' => 'string'),
      'markupBasisVersion' => array(
        'xpath' => $textXpath.'f:markupBasisVersion',
        'type' => 'string'),
      'markupLanguage' => array(
        'xpath' => $textXpath.'f:markupLanguage',
        'type' => 'string'));

    foreach ($fields as $fieldName => $options)
    {
      $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
      if (!empty($value))
      {
        $this->ioData['text'][$fieldName] = $value;
      }
    }
  }

  private function loadMediainfoData()
  {
    $trackFields = array(
      'count' => array(
        'xpath' => 's:Count',
        'type' => 'integer'),
      'videoFormatList' => array(
        'xpath' => 's:Video_Format_List',
        'type' => 'string'),
      'videoFormatWithHintList' => array(
        'xpath' => 's:Video_Format_WithHint_List',
        'type' => 'string'),
      'codecsVideo' => array(
        'xpath' => 's:Codecs_Video',
        'type' => 'string'),
      'videoLanguageList' => array(
        'xpath' => 's:Video_Language_List',
        'type' => 'string'),
      'audioFormatList' => array(
        'xpath' => 's:Audio_Format_List',
        'type' => 'string'),
      'audioFormatWithHintList' => array(
        'xpath' => 's:Audio_Format_WithHint_List',
        'type' => 'string'),
      'audioCodecs' => array(
        'xpath' => 's:Audio_codecs',
        'type' => 'string'),
      'audioLanguageList' => array(
        'xpath' => 's:Audio_Language_List',
        'type' => 'string'),
      'completeName' => array(
        'xpath' => 's:Complete_name',
        'type' => 'string'),
      'fileName' => array(
        'xpath' => 's:File_name',
        'type' => 'string'),
      'fileExtension' => array(
        'xpath' => 's:File_extension',
        'type' => 'string'),
      'format' => array(
        'xpath' => 's:Format',
        'type' => 'string'),
      'formatInfo' => array(
        'xpath' => 's:Format_Info',
        'type' => 'string'),
      'formatUrl' => array(
        'xpath' => 's:Format_Url',
        'type' => 'string'),
      'formatProfile' => array(
        'xpath' => 's:Format_profile',
        'type' => 'string'),
      'formatSettings' => array(
        'xpath' => 's:Format_settings',
        'type' => 'string'),
      'formatSettingsCabac' => array(
        'xpath' => 's:Format_settings__CABAC',
        'type' => 'string'),
      'formatSettingsReFrames' => array(
        'xpath' => 's:Format_settings__ReFrames',
        'type' => 'string'),
      'formatSettingsGop' => array(
        'xpath' => 's:Format_settings__GOP',
        'type' => 'string'),
      'formatExtensionsUsuallyUsed' => array(
        'xpath' => 's:Format_Extensions_usually_used',
        'type' => 'string'),
      'commercialName' => array(
        'xpath' => 's:Commercial_name',
        'type' => 'string'),
      'internetMediaType' => array(
        'xpath' => 's:Internet_media_type',
        'type' => 'string'),
      'codecId' => array(
        'xpath' => 's:Codec_ID',
        'type' => 'string'),
      'codecIdInfo' => array(
        'xpath' => 's:Codec_ID_Info',
        'type' => 'string'),
      'codecIdUrl' => array(
        'xpath' => 's:Codec_ID_Url',
        'type' => 'string'),
      'codec' => array(
        'xpath' => 's:Codec',
        'type' => 'string'),
      'codecFamily' => array(
        'xpath' => 's:Codec_Family',
        'type' => 'string'),
      'codecInfo' => array(
        'xpath' => 's:Codec_Info',
        'type' => 'string'),
      'codecUrl' => array(
        'xpath' => 's:Codec_Url',
        'type' => 'string'),
      'codecCc' => array(
        'xpath' => 's:Codec_CC',
        'type' => 'string'),
      'codecProfile' => array(
        'xpath' => 's:Codec_profile',
        'type' => 'string'),
      'codecSettings' => array(
        'xpath' => 's:Codec_settings',
        'type' => 'string'),
      'codecSettingsCabac' => array(
        'xpath' => 's:Codec_settings__CABAC',
        'type' => 'string'),
      'codecSettingsRefFrames' => array(
        'xpath' => 's:Codec_Settings_RefFrames',
        'type' => 'string'),
      'codecExtensionsUsuallyUsed' => array(
        'xpath' => 's:Codec_Extensions_usually_used',
        'type' => 'string'),
      'fileSize' => array(
        'xpath' => 's:File_size',
        'type' => 'firstInteger'),
      'duration' => array(
        'xpath' => 's:Duration',
        'type' => 'firstInteger'),
      'bitRate' => array(
        'xpath' => 's:Bit_rate',
        'type' => 'firstInteger'),
      'bitRateMode' => array(
        'xpath' => 's:Bit_rate_mode',
        'type' => 'string'),
      'overallBitRate' => array(
        'xpath' => 's:Overall_bit_rate',
        'type' => 'firstInteger'),
      'channels' => array(
        'xpath' => 's:Channel_s_',
        'type' => 'firstInteger'),
      'channelPositions' => array(
        'xpath' => 's:Channel_positions',
        'type' => 'string'),
      'samplingRate' => array(
        'xpath' => 's:Sampling_rate',
        'type' => 'firstInteger'),
      'samplesCount' => array(
        'xpath' => 's:Samples_count',
        'type' => 'firstInteger'),
      'compressionMode' => array(
        'xpath' => 's:Compression_mode',
        'type' => 'string'),
      'width' => array(
        'xpath' => 's:Width',
        'type' => 'firstInteger'),
      'height' => array(
        'xpath' => 's:Height',
        'type' => 'firstInteger'),
      'pixelAspectRatio' => array(
        'xpath' => 's:Pixel_aspect_ratio',
        'type' => 'firstFloat'),
      'displayAspectRatio' => array(
        'xpath' => 's:Display_aspect_ratio',
        'type' => 'firstStringWithTwoPoints'),
      'rotation' => array(
        'xpath' => 's:Rotation',
        'type' => 'firstFloat'),
      'frameRateMode' => array(
        'xpath' => 's:Frame_rate_mode',
        'type' => 'string'),
      'frameRate' => array(
        'xpath' => 's:Frame_rate',
        'type' => 'firstFloat'),
      'frameCount' => array(
        'xpath' => 's:Frame_count',
        'type' => 'firstInteger'),
      'resolution' => array(
        'xpath' => 's:Resolution',
        'type' => 'firstInteger'),
      'colorimetry' => array(
        'xpath' => 's:Colorimetry',
        'type' => 'string'),
      'colorSpace' => array(
        'xpath' => 's:Color_space',
        'type' => 'string'),
      'chromaSubsampling' => array(
        'xpath' => 's:Chroma_subsampling',
        'type' => 'string'),
      'bitDepth' => array(
        'xpath' => 's:Bit_depth',
        'type' => 'firstInteger'),
      'scanType' => array(
        'xpath' => 's:Scan_type',
        'type' => 'string'),
      'interlacement' => array(
        'xpath' => 's:Interlacement',
        'type' => 'string'),
      'bitsPixelFrame' => array(
        'xpath' => 's:Bits__Pixel_Frame_',
        'type' => 'firstFloat'),
      'streamSize' => array(
        'xpath' => 's:Stream_size',
        'type' => 'firstInteger'),
      'proportionOfThisStream' => array(
        'xpath' => 's:Proportion_of_this_stream',
        'type' => 'firstFloat'),
      'headerSize' => array(
        'xpath' => 's:HeaderSize',
        'type' => 'firstInteger'),
      'dataSize' => array(
        'xpath' => 's:DataSize',
        'type' => 'firstInteger'),
      'footerSize' => array(
        'xpath' => 's:FooterSize',
        'type' => 'firstInteger'),
      'language' => array(
        'xpath' => 's:Language',
        'type' => 'string'),
      'colorPrimaries' => array(
        'xpath' => 's:Color_primaries',
        'type' => 'string'),
      'transferCharacteristics' => array(
        'xpath' => 's:Transfer_characteristics',
        'type' => 'string'),
      'matrixCoefficients' => array(
        'xpath' => 's:Matrix_coefficients',
        'type' => 'string'),
      'isStreamable' => array(
        'xpath' => 's:IsStreamable',
        'type' => 'boolean'),
      'writingApplication' => array(
        'xpath' => 's:Writing_application',
        'type' => 'string'),
      'fileLastModificationDate' => array(
        'xpath' => 's:File_last_modification_date',
        'type' => 'date'),
      'fileLastModificationDateLocal' => array(
        'xpath' => 's:File_last_modification_date__local_',
        'type' => 'date'));

    // Get all tracks
    foreach ($this->document->xpath($this->objectXpath.'s:objectCharacteristics/s:objectCharacteristicsExtension/s:Mediainfo/s:File/s:track') as $track)
    {
      $track->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');

      $esTrack = array();

      // Load track data
      foreach ($trackFields as $fieldName => $options)
      {
        $value = $this->getFieldValue($track, $options['xpath'], $options['type']);
        if (!empty($value))
        {
          $esTrack[$fieldName] = $value;
        }
      }

      // Add track by type
      $type = $track->xpath('@type');
      switch ($type[0])
      {
        case 'General':
          $this->ioData['mediainfo']['generalTracks'][] = $esTrack;

          break;

        case 'Video':
          $this->ioData['mediainfo']['videoTracks'][] = $esTrack;

          break;

        case 'Audio':
          $this->ioData['mediainfo']['audioTracks'][] = $esTrack;

          break;
      }
    }
  }

  private function loadFormatData()
  {
    $fields = array(
      'name' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:format/s:formatDesignation/s:formatName',
        'type' => 'string'),
      'version' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:format/s:formatDesignation/s:formatVersion',
        'type' => 'string'),
      'registryName' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:format/s:formatRegistry/s:formatRegistryName',
        'type' => 'string'),
      'registryKey' => array(
        'xpath' => $this->objectXpath.'s:objectCharacteristics/s:format/s:formatRegistry/s:formatRegistryKey',
        'type' => 'string'));

    foreach ($fields as $fieldName => $options)
    {
      $value = $this->getFieldValue($this->document, $options['xpath'], $options['type']);
      if (!empty($value))
      {
        $this->ioData['format'][$fieldName] = $value;
      }
    }
  }

  private function loadEventsData($amdSecId)
  {
    $eventFields = array(
      'type' => array(
        'xpath' => 's:eventType',
        'type' => 'string'),
      'dateTime' => array(
        'xpath' => 's:eventDateTime',
        'type' => 'date'),
      'detail' => array(
        'xpath' => 's:eventDetail',
        'type' => 'string'),
      'outcome' => array(
        'xpath' => 's:eventOutcomeInformation/s:eventOutcome',
        'type' => 'string'),
      'outcomeDetailNote' => array(
        'xpath' => 's:eventOutcomeInformation/s:eventOutcomeDetail/s:eventOutcomeDetailNote',
        'type' => 'string'));

    $linkingAgentIdentifierFields = array(
      'type' => array(
        'xpath' => 's:linkingAgentIdentifierType',
        'type' => 'string'),
      'value' => array(
        'xpath' => 's:linkingAgentIdentifierValue',
        'type' => 'string'));

    // Get all events
    foreach ($this->document->xpath('//m:amdSec[@ID="'.$amdSecId.'"]/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:EVENT"]/m:xmlData/s:event') as $item)
    {
      $item->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');

      $event = array();

      // Load event data
      foreach ($eventFields as $fieldName => $options)
      {
        $value = $this->getFieldValue($item, $options['xpath'], $options['type']);
        if (!empty($value))
        {
          $event[$fieldName] = $value;
        }
      }

      // Get all event linking agent identifiers
      foreach ($item->xpath('s:linkingAgentIdentifier') as $linkingAgent)
      {
        $linkingAgent->registerXPathNamespace('s', 'info:lc/xmlns/premis-v2');

        $linkingAgentIdentifier = array();

        // Load linking agent identifier data
        foreach ($linkingAgentIdentifierFields as $fieldName => $options)
        {
          $value = $this->getFieldValue($linkingAgent, $options['xpath'], $options['type']);
          if (!empty($value))
          {
            $linkingAgentIdentifier[$fieldName] = $value;
          }
        }

        // Add linking agent identifier to event
        $event['linkingAgentIdentifier'][] = $linkingAgentIdentifier;
      }

      // Add event dateTime to IO's dateIngested field if it's the ingestion event
      if (isset($event['type']) && isset($event['dateTime']) && $event['type'] == 'ingestion')
      {
        $this->ioData['dateIngested'] = $event['dateTime'];
      }

      // Format identification event is stored apart
      if (isset($event['type']) && $event['type'] == 'format identification')
      {
        $this->ioData['formatIdentificationEvent'] = $event;
      }
      else
      {
        $this->ioData['otherEvents'][] = $event;
      }
    }
  }

  private function loadAgentsData($amdSecId)
  {
    $agentFields = array(
      'identifierType' => array(
        'xpath' => 'm:agentIdentifier/m:agentIdentifierType',
        'type' => 'string'),
      'identifierValue' => array(
        'xpath' => 'm:agentIdentifier/m:agentIdentifierValue',
        'type' => 'string'),
      'name' => array(
        'xpath' => 'm:agentName',
        'type' => 'string'),
      'type' => array(
        'xpath' => 'm:agentType',
        'type' => 'string'));

    foreach ($this->document->xpath('//m:amdSec[@ID="'.$amdSecId.'"]/m:digiprovMD/m:mdWrap[@MDTYPE="PREMIS:AGENT"]/m:xmlData/m:agent') as $item)
    {
      $item->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

      $agent = array();

      foreach ($agentFields as $fieldName => $options)
      {
        $value = $this->getFieldValue($item, $options['xpath'], $options['type']);
        if (!empty($value))
        {
          $agent[$fieldName] = $value;
        }
      }

      $this->ioData['agents'][] = $agent;
    }
  }

  private function getFieldValue($element, $xpath, $type)
  {
    if (1 > count($results = $element->xpath($xpath)))
    {
      return;
    }

    switch ($type)
    {
      case 'lastPartOfPath':
        return end(explode('/', (string)$results[0]));

      case 'string':
        return (string)$results[0];

      case 'date':
        return arElasticSearchPluginUtil::convertDate((string)$results[0]);

      case 'boolean':
        return strtolower((string)$results[0]) == 'yes' ? true : false;

      case 'integer':
        return (integer)$results[0];

      case 'firstInteger':
        foreach ($results as $item)
        {
          if (ctype_digit((string)$item))
          {
            return (integer)$item;
          }
        }

      case 'firstFloat':
        foreach ($results as $item)
        {
          if (is_float(floatval((string)$item)))
          {
            return floatval((string)$item);
          }
        }

      case 'firstStringWithTwoPoints':
        foreach ($results as $item)
        {
          if (strrpos((string)$item, ':') !== false)
          {
            return (string)$item;
          }
        }
    }
  }
}
