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
    'badArgument' => 'The request includes illegal arguments, is missing required arguments, includes a repeated argument, or values for arguments have an illegal syntax.',
    'badResumptionToken' => 'The value of the resumptionToken argument is invalid or expired.',
    'badVerb' => 'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.',
    'cannotDisseminateFormat' => 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.',
    'idDoesNotExist' => 'The value of the identifier argument is unknown or illegal in this repository.',
    'noRecordsMatch' => 'The combination of the values of the from, until, set and metadataPrefix arguments results in an empty list.',
    'noMetadataFormats' => 'There are no metadata formats available for the specified item.',
    'noSetHierarchy' => 'The repository does not support sets.'
  );

  public $oaiVerbArr = array('Identify', 'ListMetadataFormats', 'ListSets', 'ListRecords', 'ListIdentifiers', 'GetRecord');

  /**
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    sfConfig::set('sf_escaping_strategy', false);

    $appRoot = sfConfig::get('sf_root_dir');
    include($appRoot .'/vendor/symfony/lib/helper/EscapingHelper.php');

    // If authentication is enabled, check API key in HTTP header
    $authenticationRequiredSetting = QubitSetting::getByName('oai_authentication_enabled');

    // If auth key specified or authentication is required then attempt to
    // authenticate, responding with 403 if authentication fails. X-OAI-API-Key
    // is an old name but we still check for backward compatibility.
    $requestOaiApiKey = Qubit::getHttpHeader(array('OAI-API-Key', 'X-OAI-API-Key'));

    if (!empty($requestOaiApiKey) || null !== $authenticationRequiredSetting && $authenticationRequiredSetting->value)
    {
      // Require user have valid API key to access OAI data
      if (empty($requestOaiApiKey))
      {
        return QubitAcl::forwardUnauthorized(true);
      }
      else
      {
        $criteria = new Criteria;
        $criteria->add(QubitProperty::NAME, 'oaiApiKey');
        $criteria->add(QubitPropertyI18n::VALUE, $requestOaiApiKey);

        if (null == $oaiApiKeyProperty = QubitProperty::getOne($criteria))
        {
          return QubitAcl::forwardUnauthorized(true);
        }

        // Authenticate user so ACL checks can be applies in XML template# get user ID from property?
        $user = QubitUser::getById($oaiApiKeyProperty->objectId);
        $this->context->user->signIn($user);
      }
    }

    $request->setRequestFormat('xml');

    $this->date = QubitOai::getDate();
    $this->path = $this->request->getUriPrefix().$this->request->getPathInfo();
    $this->attributes = $this->request->getGetParameters();

    // If we have a valid resumption token, apply its parameters to our request
    $this->applyResumptionToken($request, $this->attributes);

    $this->attributesKeys = array_keys($this->attributes);
    $this->requestAttributes = '';
    foreach ($this->attributesKeys as $key)
    {
      $this->requestAttributes .= ' '.$key.'="'.esc_specialchars($this->attributes[$key]).'"';
    }

    // Validate that verb is valid
    if (isset($this->request->verb))
    {
      if (!in_array($this->request->verb, $this->oaiVerbArr))
      {
        $request->setParameter('errorCode', 'badVerb');
        $request->setParameter('errorMsg', 'Value of the verb argument is not a legal OAI-PMH verb, the verb argument is missing, or the verb argument is repeated.');

        $this->forward('arOaiPlugin', 'error');
      }

      // Validate that attributes are valid
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
      if ($metadataPrefix != '' && !QubitOai::checkValidMetadataFormat($metadataPrefix))
      {
        $request->setParameter('errorCode', 'cannotDisseminateFormat');
        $request->setParameter('errorMsg', 'The metadata format identified by the value given for the metadataPrefix argument is not supported by the item or by the repository.');

        $this->forward('arOaiPlugin', 'error');
      }

      // If the 'set' parameter is provided, load sets and make sure it refers to an existing set
      if ($this->request->set && !QubitOai::getMatchingOaiSet($this->request->set))
      {
        $request->setParameter('errorCode', 'badArgument');
        $request->setParameter('errorMsg', 'The requested OAI set is not known by this repository.');

        $this->forward('arOaiPlugin', 'error');
      }

      // If 'identifier' parameter is provided, make sure it refers to an existing record
      if ($this->request->identifier && null === QubitInformationObject::getRecordByOaiID(QubitOai::getOaiIdNumber($this->request->identifier)))
      {
        $request->setParameter('errorCode', 'idDoesNotExist');
        $request->setParameter('errorMsg', 'The value of the identifier argument is unknown or illegal in this repository.');

        $this->forward('arOaiPlugin', 'error');
      }

      switch ($this->request->verb)
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
    }
    else
    {
      $this->verb = 'badVerb';
    }
  }

  private function applyResumptionToken($request, &$attributes)
  {
    if (!isset($request->resumptionToken))
    {
      return;
    }

    $resumptionTokenJson = base64_decode($request->resumptionToken);
    $resumptionToken = $resumptionTokenJson ? json_decode($resumptionTokenJson) : False;

    if ($resumptionToken === False)
    {
      $this->sendResumptionTokenError($request);
    }

    $settableAttributes = array('from', 'until', 'cursor', 'set', 'metadataPrefix');

    foreach ($settableAttributes as $attribute)
    {
      if ($resumptionToken->$attribute)
      {
        // We set each attribute both on the request and on our attributes list.
        // The attributes list is checked by QubitOai::checkBadArgument, while
        // $request is used by the call to arOaiPluginComponent.
        $attributes[$attribute] = $resumptionToken->$attribute;
        $request->$attribute = $resumptionToken->$attribute;
      }
    }
  }

  private function sendResumptionTokenError($request)
  {
    $request->setParameter('errorCode', 'badResumptionToken');
    $request->setParameter('errorMsg', 'Value of the resumptionToken argument is invalid.');

    $this->forward('arOaiPlugin', 'error');
  }
}
