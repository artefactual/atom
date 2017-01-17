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
 * Repository - showIsdiah
 *
 * @package    AccesstoMemory
 * @subpackage Actor - initialize a showIDIAH template for displaying a repository
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfIsdiahPluginIndexAction extends RepositoryIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->isdiah = new sfIsdiahPlugin($this->resource);

    if (null !== $this->resource->id &&
      sfConfig::get('app_enable_institutional_scoping'))
    {
      // Add repo to the user session as realm
      $this->context->user->setAttribute('search-realm', $this->resource->id);
    }

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->htmlSnippet = $this->getPurifiedHtmlSnippet();

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->authorizedFormOfName = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Authorized form of name%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-3#5.1.2">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-3#4.7">', '%4%' => '</a>'))));
      $values['authorizedFormOfName'] = $this->resource->getAuthorizedFormOfName(array('culltureFallback' => true));

      $validatorSchema->identifier = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Identifier%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-3#5.1.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-3#4.7">', '%4%' => '</a>'))));
      $values['identifier'] = $this->resource->identifier;

      $validatorSchema->primaryContact = new sfValidatorAnd(array(
        new QubitValidatorCountable,
        new sfValidatorOr(array(
          new sfValidatorSchema(array('city' => new sfValidatorString(array('required' => true))), array('allow_extra_fields' => true)),
          new sfValidatorSchema(array('countryCode' => new sfValidatorString(array('required' => true))), array('allow_extra_fields' => true)),
          new sfValidatorSchema(array('postalCode' => new sfValidatorString(array('required' => true))), array('allow_extra_fields' => true)),
          new sfValidatorSchema(array('region' => new sfValidatorString(array('required' => true))), array('allow_extra_fields' => true)),
          new sfValidatorSchema(array('streetAddress' => new sfValidatorString(array('required' => true))), array('allow_extra_fields' => true))), array(
          'required' => true), array(
          'invalid' => $this->context->i18n->__('%1%Contact information%2% - You %3%must%4% at least include one of the following location or address fields: city, country, postal code, region or street address.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-3#5.2.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-3#4.7">', '%4%' => '</a>'))))), array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Contact information%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-3#5.2.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-3#4.7">', '%4%' => '</a>'))));

      if (null !== $this->resource->getPrimaryContact())
      {
        $values['primaryContact']['city'] = $this->resource->getPrimaryContact()->getCity(array('culltureFallback' => true));
        $values['primaryContact']['countryCode'] = $this->resource->getPrimaryContact()->countryCode;
        $values['primaryContact']['postalCode'] = $this->resource->getPrimaryContact()->postalCode;
        $values['primaryContact']['region'] = $this->resource->getPrimaryContact()->getRegion(array('culltureFallback' => true));
        $values['primaryContact']['streetAddress'] = $this->resource->getPrimaryContact()->streetAddress;
      }

      try
      {
        $validatorSchema->clean($values);
      }
      catch (sfValidatorErrorSchema $e)
      {
        $this->errorSchema = $e;
      }
    }

    if (null !== $contact = $this->resource->getPrimaryContact())
    {
      if (isset($contact->latitude) && isset($contact->longitude))
      {
        $this->latitude = $contact->latitude;
        $this->longitude = $contact->longitude;
      }
    }
  }

  protected function getPurifiedHtmlSnippet()
  {
    $cacheKey = 'repository:htmlsnippet:'.$this->resource->id;
    $cache = QubitCache::getInstance();

    if (null === $cache)
    {
      return;
    }

    if ($cache->has($cacheKey))
    {
      return $cache->get($cacheKey);
    }

    $content = $this->resource->getHtmlSnippet();
    $content = QubitHtmlPurifier::getInstance()->purify($content);

    $cache->set($cacheKey, $content);

    return $content;
  }
}
