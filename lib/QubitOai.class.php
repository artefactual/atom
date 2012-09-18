<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Shared functions for OAI-PMH module
 *
 * @package    qubit
 * @subpackage lib
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class QubitOai
{
  /**
   * Mail error report
   *
   * @param string $msg an error message
   * @return bool true for message sent properly
   */

  public static function mailErrors($msg)
  {
    $to      = QubitOai::getAdminEmail();
    $from    = $to;
    $subject = 'Qubit OAI-PMH Harvest';
    $message = $msg;
    $headers = "From: {QubitOai::getAdminEmail()}\r\nX-Mailer: PHP/".phpversion();
    $params = sprintf('-oi -f %s', $from);
    return mail($to, $subject, $message, $headers, $params);
  }

  /**
   * Admin email finder
   *
   * @return string the administrator email
   */
  public static function getAdminEmail()
  {
    $criteria = new Criteria;
    $criteria->addJoin(QubitUser::ID, QubitUserRoleRelation::USER_ID);
    $criteria->addJoin(QubitUserRoleRelation::ROLE_ID, QubitRole::ID);
    $criteria->add(QubitRole::NAME, 'administrator');
    $criteria->addAscendingOrderByColumn(QubitUser::ID);
    $users = QubitUser::get($criteria);
    return trim($users[0]->getEmail());
  }

  /**
   * Check that all supplied keys are valid for the provided request
   *
   * @param array    $keys submited request's keys
   * @param array $requestValidKeys valid keys
   * @return bool are the attributes valid or not
   */

  public static function checkBadArgument($keys, $requestValidKeys, $mandatoryKeys)
  {
    //Check that only valid keys are present
    foreach ($keys as $key)
    {
      if (!in_array($key, $requestValidKeys))
      {
        return false;
      }
    }
    //Check that all mandatory keys are present
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
   * Check if there are null values in the parameters
   *
   * @param array    $parameters query's parameters
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
   * @param array    $date submited date
   * @return bool true if the date is valid
   */
  public static function isValidDate($date)
  {
    // ex 2003-01-02T01:30:30Z or 2003-01-02
    $parts = explode('-', $date);
    if (count($parts) != 3)
    {
      return false;
    }
    // if time is part of the date then validate
    if ($T_pos = strpos($parts[2], 'T'))
    {
      $time = substr($parts[2], $T_pos);
      $parts[2] = substr($parts[2], 0, $T_pos);
      if (!preg_match('/^T(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]Z$/i', $time))
      {
        return false;
      }
    }

    // parameters - Month, Day, Year
    if (!@checkdate($parts[1], $parts[2], $parts[0]))
    {
      return false;
    }

    // if validation got this far then return true (valid date)
    return true;
  }

  /**
   * Validates OaiKey
   *
   * @param string    $key the oai key
   * @return bool true if the date is valid
   */
  public static function isValidOaiKey($key)
  {
    if (isset($validOaiKeyArr[$key]))
    {
      return true;
    }
    return false;
  }

  /**
   * Extracts the base URL form the _SERVER global
   *
   * @return string base URL
   */
  public static function getBaseUrl()
  {
    $baseURL = 'http://'.$_SERVER['SERVER_NAME'];
    if ($_SERVER['SERVER_PORT'] != '80')
    {
      $baseURL .= ':'.$_SERVER['SERVER_PORT'];
    }
    return $baseURL.$_SERVER['SCRIPT_NAME'];
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
    $metadataFormats = array(array('prefix'=>'oai_dc', 'namespace'=>'http://www.openarchives.org/OAI/2.0/oai_dc/', 'schema'=>'http://www.openarchives.org/OAI/2.0/oai_dc.xsd'));
    return $metadataFormats;
  }

  /**
   * Load array of collections
   *
   * @return array associative array of collection information
   */
  public static function getCollectionArray()
  {
    $collections = QubitInformationObject::getCollections();
    $collectionTable = array();

    foreach ($collections as $collection)
    {
      $collectionTable[] = array('setSpec'=>$collection->getOaiIdentifier(), 'lft' => $collection->getLft(), 'rgt' => $collection->getRgt());
    }
    return $collectionTable;
  }

  /**
   * Returns collection identifier for the element with $left element
   *
   * @param int $left left side of information object
   * @return string oai identifier of the element's collection
   */
  public static function getSetSpec($left, $collectionTable)
  {
    foreach ($collectionTable as $collection)
    {
      if ($collection['lft'] <= $left AND $collection['rgt'] > $left)
      {
        return $collection['setSpec'];
      }
    }
    return 'None';
  }

  /**
   * Returns collection info for the element with $setSpec
   *
   * @param int $setSpec left side of information object
   * @return string oai identifier of the element's collection
   */
  public static function getCollectionInfo($setSpec, $collectionsTable)
  {
    foreach ($collectionsTable as $collection)
    {
      if ($collection['setSpec'] == $setSpec)
      {
        return $collection;
      }
    }
    return false;
  }

  /**
   * Gets limits of the collection
   *
   * @param string $setSpec the collection id
   * @return array associative array of collection with setSpec
   */
  public static function getCollectionLimits($setSpec)
  {
    $collectionTable = oai::getCollectionArray();
    foreach ($collectionTable as $collection)
    {
      if ($collection['setSpec'] == $setSpec)
      {
        return $collection;
      }
    }
    return false;
  }

  public static function getRepositoryIdentifier()
  {
    $repositoryIdentifier = sfContext::getInstance()->request->getHost();
    if ($repositoryCode = sfConfig::get('app_oai_oai_repository_code'))
    {
      $repositoryIdentifier .= ':'.$repositoryCode;
    }

    return $repositoryIdentifier;
  }

  public static function getSampleIdentifier()
  {
    $sampleIdentifier = sfContext::getInstance()->request->getHost().':';
    if ($repositoryCode = sfConfig::get('app_oai_oai_repository_code'))
    {
      $sampleIdentifier .= $repositoryCode;
    }
    $sampleIdentifier .= '_100002';

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
    return $result[1];
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
   * modified helper methods from (http://www.php.net/manual/en/ref.dom.php):
   *
   * - create a DOMDocument from a file
   * - parse the namespaces in it
   * - create a XPath object with all the namespaces registered
   *  - load the schema locations
   *  - validate the file on the main schema (the one without prefix)
   */
  public static function loadXML($XMLString)
  {
    libxml_use_internal_errors(true);

    // FIXME: trap possible load validation errors (just suppress for now)
    //$err_level = error_reporting(0);

    $doc = new DOMDocument('1.0', 'UTF-8');

    // enforce all XML parsing rules and validation
    $doc->validateOnParse = true;
    $doc->resolveExternals = true;

    $doc->formatOutput = false;
    $doc->preserveWhitespace = false;

    $doc->loadXML($XMLString);

    $xsi = false;
    $doc->namespaces = array();
    $doc->xpath = new DOMXPath($doc);

    // pass along any XML errors that have been generated
    $doc->libxmlerrors = libxml_get_errors();

    // if the document didn't parse correctly, stop right here
    if (empty($doc->documentElement))
    {
      return $doc;
    }

    //error_reporting($err_level);

    // look through the entire document for namespaces
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

      // validate document against default namespace schema
      $doc->schemaValidate($doc->schemaLocations[$doc->namespaces['']]);
    }

    return $doc;
  }
}
