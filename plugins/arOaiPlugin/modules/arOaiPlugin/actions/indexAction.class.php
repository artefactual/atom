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
 * Generate the OAI-PMH response
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class arOaiPluginIndexAction extends sfAction
{
  public $oaiErrorArr = array(
    'badArgument'=>'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.',
    'badResumptionToken'=>'The value of the resumptionToken argument is invalid or expired.',
    'badVerb'=>'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.',
    'cannotDisseminateFormat'=>'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.',
    'idDoesNotExist'=>'The value of the identifier argument is unknown or illegal in this repository.',
    'noRecordsMatch'=>'The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.',
    'noMetadataFormats'=>'There are no metadata formats available for the specified item.',
    'noSetHierarchy'=>'The repository does not support sets.'
  );

  public $oaiVerbArr = array('Identify', 'ListMetadataFormats', 'ListSets', 'ListRecords', 'ListIdentifiers', 'GetRecord');

  /**
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    $appRoot = dirname(__FILE__) .'/../../../../..';
    include($appRoot .'/vendor/symfony/lib/helper/EscapingHelper.php');

    // only respond to OAI requests if the feature has been enabled
    if (QubitSetting::getByName('oai_enabled')->value == 0)
    {
      // the following displays a GUI response, should we return a
      // '503 - Service unavailable' HTTP response (without specifying
      // a 'Retry-After' parameter instead?
      $this->forward('admin', 'oaiDisabled');
    }

    $request->setRequestFormat('xml');
    /*    print_r($this->oaiErrorArray);
    //Check for null and duplicate parameters
    if(QubitOai::hasDuplicateOrNullParameters())
    {
      $request->setParameter('errorCode', 'badArgument');
      $request->setParameter('errorMsg', $this->oaiErrorArray{'badArgument'});
      $this->forward('arOaiPlugin', 'error');
    }*/
    $this->date = QubitOai::getDate();
    $this->path = $this->request->getUriPrefix().$this->request->getPathInfo();
    $this->attributes = $this->request->getGetParameters();

    /* allow resumption token to set attributes */
    if (isset($this->attributes['resumptionToken']))
    {
      $stateChange = explode('&', $this->attributes['resumptionToken']);
      foreach($stateChange as $keyValuePair)
      {
        $resumptionTokenError = False;

        if (substr_count($keyValuePair, '='))
        {
          list($attribute, $value) = explode('=', $keyValuePair);

          switch ($attribute)
          {
            case 'from':
              $request->from = $value;
              break;

            case 'until':
              $request->until = $value;
              break;

            case 'cursor':
              $request->cursor = $value;
              break;

            default:
              $resumptionTokenError = True;
          }
        } else {
          $resumptionTokenError = True;
        }

        if ($resumptionTokenError)
        {
          $request->setParameter('errorCode', 'badResumptionToken');
          $request->setParameter('errorMsg', 'Value of the resumptionToken argument is invalid.');
          $this->forward('arOaiPlugin', 'error');
        }
      }
    }

    $this->attributesKeys = array_keys($this->attributes);
    $this->requestAttributes = '';
    foreach ($this->attributesKeys as $key)
    {
      $this->requestAttributes .= ' '.$key.'="'.esc_specialchars($this->attributes[$key]).'"';
    }
    $this->sets = array();

    foreach (QubitInformationObject::getCollections() as $el)
    {
      $this->sets[] = new sfIsadPlugin($el);
    }

    /**
     * Validate that verb is valid
    */
    if (isset($this->request->verb))
    {
      if (!in_array($this->request->verb, $this->oaiVerbArr))
      {
        $request->setParameter('errorCode', 'badVerb');
        $request->setParameter('errorMsg', 'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.');
        $this->forward('arOaiPlugin', 'error');
      }

      /**
       * Validate that attributes are valid
      */
      $allowedKeys = sfConfig::get('mod_aroaiplugin_'.$this->request->verb.'Allowed');
      $mandatoryKeys = sfConfig::get('mod_aroaiplugin_'.$this->request->verb.'Mandatory');
      if (!QubitOai::checkBadArgument($this->attributesKeys, $allowedKeys, $mandatoryKeys))
      {
        $request->setParameter('errorCode', 'badArgument');
        $request->setParameter('errorMsg', 'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.');
        $this->forward('arOaiPlugin', 'error');
      }

      // For now, if there is a metadataPrefix requested other than oai_dc, fail the request
      $metadataPrefix = $this->request->metadataPrefix;
      if ($metadataPrefix != '' AND $metadataPrefix != 'oai_dc')
      {
        $request->setParameter('errorCode', 'badVerb');
        $request->setParameter('errorMsg', 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.');
        $this->forward('arOaiPlugin', 'error');
      }

      switch($this->request->verb)
      {
        case 'Identify':
          $this->verb = 'identify';
          break;
        case 'ListMetadataFormats':
          $this->verb = 'listMetadataFormats';
          break;
        case 'ListSets':
          $this->verb = 'listSets';
          break;
        case 'ListRecords':
          $this->verb = 'listRecords';
          break;
        case 'ListIdentifiers':
          $this->verb = 'listIdentifiers';
          break;
        case 'GetRecord':
          $this->verb = 'getRecord';
          break;
        default:
          $this->verb = 'badVerb';
      }
    } else
    {
      $this->verb = 'badVerb';
    }
  }
}
