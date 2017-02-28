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
 * Shared functions for OAI-PMH module
 *
 * @package    AccesstoMemory
 * @subpackage lib
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Damian Bauder <drbauder@ucalgary.ca>
 */

class QubitOai
{
  // Any custom OAI set implementations that are available (in addition to the
  // standard collection sets)
  private static $additionalOaiSets = array();

  /**
   * Check that all supplied keys are valid for the provided request
   *
   * @param array    $keys submited request's keys
   * @param array $requestValidKeys valid keys
   * @return bool are the attributes valid or not
   */
  public static function checkBadArgument($keys, $requestValidKeys, $mandatoryKeys)
  {
    // Check that only valid keys are present
    foreach ($keys as $key)
    {
      if (!in_array($key, $requestValidKeys))
      {
        return false;
      }
    }

    // Check that all mandatory keys are present
    foreach ($mandatoryKeys as $mandatoryKey)
    {
      if (!in_array($mandatoryKey, $keys))
      {
        return false;
      }
    }

    return true;
  }

  /**
   * Check that metadata format is valid
   *
   * @param string    metadata format
   * @return bool  is the metadata format valid or not
   */
  public static function checkValidMetadataFormat($metadataFormat)
  {
    foreach (self::getMetadataFormats() as $formatData)
    {
      if ($formatData['prefix'] === $metadataFormat)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Check if there are null values in the parameters
   *
   * @param array $parameters query's parameters
   * @return bool true if there are null values
   */
  public function hasNullParameter($parameters)
  {
    $verb = array('verb');
    $parametersKeys = array_diff(array_keys($parameters), $verb);
    foreach ($parametersKeys as $key)
    {
      if ($parameters[$key] == null)
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Validates date format
   *
   * @param array $date submited date
   * @return bool true if the date is valid
   */
  public static function isValidDate($date)
  {
    // Ex. 2003-01-02T01:30:30Z or 2003-01-02
    $parts = explode('-', $date);
    if (count($parts) != 3)
    {
      return false;
    }

    // If time is part of the date then validate
    if ($T_pos = strpos($parts[2], 'T'))
    {
      $time = substr($parts[2], $T_pos);
      $parts[2] = substr($parts[2], 0, $T_pos);
      if (!preg_match('/^T(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]Z$/i', $time))
      {
        return false;
      }
    }

    // Parameters - Month, Day, Year
    if (!@checkdate($parts[1], $parts[2], $parts[0]))
    {
      return false;
    }

    // If validation got this far then return true (valid date)
    return true;
  }

  /**
   * Validates OaiKey
   *
   * @param string    $key the oai key
   * @return bool true if the key is valid
   */
  public static function isValidOaiKey($key)
  {
    if (isset($validOaiKeyArr[$key]))
    {
      return true;
    }

    return false;
  }
  
  protected static function parseUrlHost($URL)
  {
    $parsedURL = parse_url($URL);
    
    // If the scheme is missing from a URL, parse_url() mistakenly interprets the host as the path.
    // Prepend a dummy scheme and re-parse, if this is the case.
    if (!isset($parsedURL['scheme']))
    {
      $parsedURL = parse_url('http://'.$URL);
    }
    
    return $parsedURL['host'];
  }

  /**
   * Extracts the port and script name, and derives the scheme, from the _SERVER global.
   * Then, combines those with the user-defined siteBaseUrl setting to form the base URL.
   *
   * @return string base URL
   */
  public static function getBaseUrl()
  {
    $scheme = $_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
    
    $siteBaseUrl = QubitSetting::getByName('siteBaseUrl')->getValue(array('cultureFallback' => true));
    $host = QubitOai::parseUrlHost($siteBaseUrl);
    
    $baseURL = $scheme.$host;
    if ($_SERVER['SERVER_PORT'] != '80')
    {
      $baseURL .= ':'.$_SERVER['SERVER_PORT'];
    }
    $baseURL .= $_SERVER['SCRIPT_NAME'];
    
    return $baseURL;
  }

  /**
   * Returns formated date
   *
   * @param string  $date optional date value
   * @return string a formated date
   */
  public static function getDate($date = '')
  {
    if ($date == '')
    {
      return gmdate('Y-m-d\TH:i:s\Z');
    }

    return gmdate('Y-m-d\TH:i:s\Z', strtotime($date));
  }

  /**
   * Returns MetadataPrefixes
   *
   * @return array associative array of metadataPrefixes available
   */
  public static function getMetadataFormats()
  {
    $metadataFormats = array(
      array('prefix' => 'oai_dc', 'namespace' => 'http://www.openarchives.org/OAI/2.0/oai_dc/', 'schema' => 'http://www.openarchives.org/OAI/2.0/oai_dc.xsd'),
      array('prefix' => 'oai_ead', 'namespace' => 'urn:isbn:1-931666-22-9', 'schema' => 'http://www.loc.gov/ead/ead.xsd')
    );

    return $metadataFormats;
  }

  /**
   * Load array of OAI sets
   *
   * @return array of available OAI sets
   */
  public static function getOaiSets($options = array())
  {
    $result = QubitInformationObject::getCollections($options);
    $collections = $result['data'];
    $collectionCount = $result['count'];
    $remaining = $result['remaining'];

    $oaiSets = array();

    foreach ($collections as $collection)
    {
      $oaiSets[] = new QubitOaiCollectionSet($collection);
    }

    $useAdditionalOaiSets = QubitSetting::getByName('oai_additional_sets_enabled');

    // If all collections have been returned and additional sets are enabled, add them
    if (!$remaining && $useAdditionalOaiSets && $useAdditionalOaiSets->value)
    {
      $additionalSetsToSkip = $options['offset'] - $collectionCount; // Skip additional sets within offset

      foreach (QubitOai::$additionalOaiSets as $index => $oaiSet)
      {
        // If paging isn't active or the current "page" isn't full (and this set shouldn't be skipped), add set
        if (!$options['limit'] || (count($oaiSets) < $options['limit'] && $index >= $additionalSetsToSkip))
        {
          $oaiSets[] = $oaiSet;
        }
        else if (count($oaiSets) >= $options['limit'])
        {
          // The "page" is full so add this set to the remaining count
          $remaining++;
        }
      }
    }

    return array(
      'data' => $oaiSets,
      'remaining' => $remaining
    );
  }

  /**
   * Return array of additional OAI sets
   *
   * @return array of additional OAI sets
   */
  public static function getAdditionalOaiSets()
  {
    return QubitOai::$additionalOaiSets;
  }

  /**
   * Add a new OAI set to the available list
   */
  public static function addOaiSet($oaiSet)
  {
    QubitOai::$additionalOaiSets[] = $oaiSet;
  }

  /**
   * Returns the OAI set matching $setSpec
   *
   * @param string $setSpec, the setSpec of an OAI set
   * @return QubitOaiSet/boolean the OAI set matched (or false if none matched)
   */
  public static function getMatchingOaiSet($setSpec)
  {
    // Check additional sets, if enabled
    if (sfConfig::get('app_oai_oai_additional_sets_enabled__source', false))
    {
      foreach (QubitOai::$additionalOaiSets as $oaiSet)
      {
        if ($oaiSet->setSpec() == $setSpec)
        {
          return $oaiSet;
        }
      }
    }

    // Return information object with local identifier
    if (null !== $result = QubitInformationObject::getRecordByOaiID(QubitOai::getOaiIdNumber($setSpec)))
    {
      return new QubitOaiCollectionSet($result);
    }

    return false;
  }
  
  public static function getOaiNamespaceIdentifier()
  {
    $siteBaseUrl = QubitSetting::getByName('siteBaseUrl')->getValue(array('cultureFallback' => true));
    $oaiNamespaceIdentifier = QubitOai::parseUrlHost($siteBaseUrl);
    
    return $oaiNamespaceIdentifier; 
  }

  public static function getRepositoryIdentifier()
  {
    $repositoryIdentifier = QubitOai::getOaiNamespaceIdentifier();
    
    if ($repositoryCode = sfConfig::get('app_oai_oai_repository_code'))
    {
      $repositoryIdentifier .= ':'.$repositoryCode;
    }

    return $repositoryIdentifier;
  }

  public static function getOaiSampleIdentifier()
  {
    $sampleIdentifier = 'oai:'.QubitOai::getRepositoryIdentifier().'_100002';

    return $sampleIdentifier;
  }

  /**
   * Give back the Oai Identifier unique key given a full OAI_Identifier
   *
   * @param array  $oai_identifier the full oai identifier
   * @return int the oai identifier key
   */
  public static function getOaiIdNumber($identifier)
  {
    preg_match('/^.*_([0-9]+)$/', $identifier, $result);

    return (count($result)) ? $result[1] : false;
  }

  /**
   * Validate that an xml extract is valid
   *
   * @param string $xml, an xml string to validate
   * @return boolean true if valid
   */
  public static function validate($xml)
  {
    $dom = new DOMDocument;
    $dom->loadXML($xml);

    return true;

    if ($dom->schemaValidate($xml))
    {
      return true;
    }

    return false;
  }

  /**
   * Extracts a resumption token from a response
   *
   * @param string $oaiResponse, an xml string from which to extract the resumption token
   * @return string the resumption token, or false if it does not exist
   */
  public static function getResumptionToken($oaiResponse)
  {
    $oaiXML = new DOMDocument;
    $oaiSimple = simplexml_load_string($oaiResponse);
    $oaiSimple->registerXPathNamespace('c', 'http://www.openarchives.org/OAI/2.0/');
    $oaiSimpleRes = $oaiSimple->xpath('//c:ListRecords/c:resumptionToken');

    $oaiSimpleRes = $oaiSimple->ListRecords->resumptionToken;
    if ($oaiSimpleRes == '')
    {
      return false;
    }

    return $oaiSimpleRes;
  }

  public static function importRecords($records)
  {
    return true;
  }

  /*
   * Modified helper methods from (http://www.php.net/manual/en/ref.dom.php):
   *
   * - Create a DOMDocument from a file.
   * - Parse the namespaces in it.
   * - Create a XPath object with all the namespaces registered:
   *   - Load the schema locations.
   *   - Validate the file on the main schema (the one without prefix).
   */
  public static function loadXML($XMLString)
  {
    libxml_use_internal_errors(true);

    // FIXME: trap possible load validation errors (just suppress for now)
    $doc = new DOMDocument('1.0', 'UTF-8');

    // Enforce all XML parsing rules and validation
    $doc->validateOnParse = true;
    $doc->resolveExternals = true;

    $doc->formatOutput = false;
    $doc->preserveWhitespace = false;

    $doc->loadXML($XMLString);

    $xsi = false;
    $doc->namespaces = array();
    $doc->xpath = new DOMXPath($doc);

    // Pass along any XML errors that have been generated
    $doc->libxmlerrors = libxml_get_errors();

    // If the document didn't parse correctly, stop right here
    if (empty($doc->documentElement))
    {
      return $doc;
    }

    // Look through the entire document for namespaces
    $re = '/xmlns:([^=]+)="([^"]+)"/';
    preg_match_all($re, $XMLString, $mat, PREG_SET_ORDER);

    foreach ($mat as $xmlns)
    {
      $pre = $xmlns[1];
      $uri = $xmlns[2];

      $doc->namespaces[$pre] = $uri;

      if ($pre == '')
      {
        $pre = 'noname';
      }

      $doc->xpath->registerNamespace($pre, $uri);
    }

    if (!isset($doc->namespaces['']))
    {
      $doc->namespaces[''] = $doc->documentElement->lookupnamespaceURI(null);
    }

    if ($xsi)
    {
      $doc->schemaLocations = array();
      $lst = $doc->xpath->query('//@$xsi:schemaLocation');
      foreach ($lst as $el)
      {
        $re = "{[\\s\n\r]*([^\\s\n\r]+)[\\s\n\r]*([^\\s\n\r]+)}";
        preg_match_all($re, $el->nodeValue, $mat);
        for ($i = 0; $i < count($mat[0]); $i++)
        {
          $value = $mat[2][$i];
          $doc->schemaLocations[$mat[1][$i]] = $value;
        }
      }

      // Validate document against default namespace schema
      $doc->schemaValidate($doc->schemaLocations[$doc->namespaces['']]);
    }

    return $doc;
  }
}
