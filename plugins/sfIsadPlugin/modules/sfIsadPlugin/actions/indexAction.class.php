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
 * Information Object - showIsad
 *
 * @package    AccesstoMemory
 * @subpackage informationObject - initialize a showIsad template for displaying an information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 */

class sfIsadPluginIndexAction extends InformationObjectIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->isad = new sfIsadPlugin($this->resource);

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    // Function relations
    $criteria = new Criteria;
    $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
    $criteria->addJoin(QubitRelation::SUBJECT_ID, QubitFunction::ID);

    $this->functionRelations = QubitRelation::get($criteria);

    // Set creator history label
    $this->creatorHistoryLabels = array(
      NULL => $this->context->i18n->__('Administrative / Biographical history'),
      QubitTerm::CORPORATE_BODY_ID => $this->context->i18n->__('Administrative history'),
      QubitTerm::PERSON_ID => $this->context->i18n->__('Biographical history'),
      QubitTerm::FAMILY_ID => $this->context->i18n->__('Biographical history')
    );

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->creators = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('This archival description, or one of its higher levels, %1%requires%2% at least one %3%creator%4%.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#3.2.1">', '%4%' => '</a>'))));

      foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $item)
      {
        $values['creators'] = $item->getCreators();
        if (0 < count($values['creators']))
        {
          break;
        }
      }

      $validatorSchema->dateRange = new QubitValidatorDates(array(), array(
        'invalid' => $this->context->i18n->__('%1%Date(s)%2% - are not consistent with %3%higher levels%2%.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.3">', '%2%' => '</a>', '%3%' => '<a href="%ancestor%">'))));
      $values['dateRange'] = $this->resource;

      $validatorSchema->dates = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Date(s)%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.3">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%4%' => '</a>'))));
      $values['dates'] = $this->resource->getDates();

      $validatorSchema->extentAndMedium = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Extent and medium%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.5">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%4%' => '</a>'))));
      $values['extentAndMedium'] = $this->resource->getExtentAndMedium(array('cultureFallback' => true));

      $validatorSchema->identifier = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Identifier%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.1">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%4%' => '</a>'))));
      $values['identifier'] = $this->resource->identifier;

      $this->addField($validatorSchema, 'levelOfDescription');
      $validatorSchema->levelOfDescription->setMessage('forbidden', $this->context->i18n->__('%1%Level of description%2% - Value "%value%" is not consistent with higher levels.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.4">', '%2%' => '</a>')));
      $validatorSchema->levelOfDescription->setMessage('required', $this->context->i18n->__('%1%Level of description%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.4">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%4%' => '</a>')));

      if (isset($this->resource->levelOfDescription))
      {
        $values['levelOfDescription'] = $this->resource->levelOfDescription->getName(array('sourceCulture' => true));
      }

      $validatorSchema->title = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('%1%Title%2% - This is a %3%mandatory%4% element.', array('%1%' => '<a href="http://ica-atom.org/doc/RS-1#3.1.2">', '%2%' => '</a>', '%3%' => '<a href="http://ica-atom.org/doc/RS-1#I.12">', '%4%' => '</a>'))));
      $values['title'] = $this->resource->getTitle(array('cultureFallback' => true));

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
