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

class sfIsdfPluginIndexAction extends FunctionIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->isdf = new sfIsdfPlugin($this->resource);

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->type = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Type%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-4#Type_of_description">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-4#Structure_and_use_4.7">', '%4%' => '</a>'))));
      $values['type'] = $this->resource->type;

      $validatorSchema->authorizedFormOfName = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Authorized form of name%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-4#Authorised_name">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-4#Structure_and_use_4.7">', '%4%' => '</a>'))));
      $values['authorizedFormOfName'] = $this->resource->getAuthorizedFormOfName(array('cultureFallback' => true));

      $validatorSchema->descriptionIdentifier = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Description identifier%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-4#Function.2Factivity_description_identifier">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-4#Structure_and_use_4.7">', '%4%' => '</a>'))));
      $values['descriptionIdentifier'] = $this->resource->descriptionIdentifier;

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
