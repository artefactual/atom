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
 * Actor - showIsaar
 *
 * @package    AccesstoMemory
 * @subpackage Actor - initialize an showISAAR template for displaying an actor
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfIsaarPluginIndexAction extends ActorIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    if (sfConfig::get('app_enable_institutional_scoping'))
    {
      // remove search-realm
      $this->context->user->removeAttribute('search-realm');
    }

    $this->isaar = new sfIsaarPlugin($this->resource);

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->authorizedFormOfName = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Authorized form of name%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-2#5.1.2">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-2#4.7">', '%4%' => '</a>'))));
      $values['authorizedFormOfName'] = $this->resource->getAuthorizedFormOfName(array('cultureFallback' => true));

      $validatorSchema->datesOfExistence = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Dates of existence%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-2#5.2.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-2#4.7">', '%4%' => '</a>'))));
      $values['datesOfExistence'] = $this->resource->getDatesOfExistence(array('cultureFallback' => true));

      $validatorSchema->descriptionIdentifier = new sfValidatorAnd(array(
        new sfValidatorString(
          array('required' => true),
          array('required' => $this->context->i18n->__('%1%Authority record identifier%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-2#5.4.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-2#4.7">', '%4%' => '</a>')))
        ),
        new QubitValidatorActorDescriptionIdentifier(array('resource' => $this->resource))
      ));
      $values['descriptionIdentifier'] = $this->resource->descriptionIdentifier;

      $validatorSchema->entityType = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Type of entity%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-2#5.1.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-2#4.7">', '%4%' => '</a>'))));
      $values['entityType'] = $this->resource->entityType;

      try
      {
        $validatorSchema->clean($values);
      }
      catch (sfValidatorErrorSchema $e)
      {
        $this->errorSchema = $e;
      }
    }
  }
}
