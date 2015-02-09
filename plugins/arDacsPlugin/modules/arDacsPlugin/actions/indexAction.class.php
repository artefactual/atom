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

class arDacsPluginIndexAction extends InformationObjectIndexAction
{
  public function execute($request)
  {
    parent::execute($request);

    $this->dacs = new arDacsPlugin($this->resource);

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

      $validatorSchema->identifier = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Identifier - This is a mandatory element.')));
      $values['identifier'] = $this->resource->identifier;

      $validatorSchema->repository = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Name and location of repository - This is a mandatory element.')));
      if (null !== $repository = $this->resource->repository)
      {
        $values['repository'] = $repository;
      }

      $validatorSchema->title = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Title - This is a mandatory element.')));
      $values['title'] = $this->resource->getTitle(array('cultureFallback' => true));

      $validatorSchema->dateRange = new QubitValidatorDates(array(), array(
        'invalid' => $this->context->i18n->__('Date(s) - are not consistent with higher levels.')));
      $values['dateRange'] = $this->resource;

      $validatorSchema->dates = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Date(s) - This is a mandatory element.')));
      $values['dates'] = $this->resource->getDates();

      $validatorSchema->extentAndMedium = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Extent - This is a mandatory element.')));
      $values['extentAndMedium'] = $this->resource->getExtentAndMedium(array('cultureFallback' => true));

      $validatorSchema->creators = new QubitValidatorCountable(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('This archival description, or one of its higher levels, requires at least one creator.')));
      foreach ($this->resource->ancestors->andSelf()->orderBy('rgt') as $item)
      {
        $values['creators'] = $item->getCreators();
        if (0 < count($values['creators']))
        {
          break;
        }
      }

      $validatorSchema->scopeAndContent = new sfValidatorString(array(
        'required' => true), array(
        'required' => $this->context->i18n->__('Scope and content - This is a mandatory element.')));
      $values['scopeAndContent'] = $this->resource->getScopeAndContent(array('cultureFallback' => true));

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
