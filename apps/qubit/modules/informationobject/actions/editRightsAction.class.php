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

class InformationObjectEditRightsAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'id',
      'act',
      'basis',
      'endDate',
      'startDate',
      'restriction',
      'rightsHolder',
      'rightsNote',
      'copyrightStatus',
      'copyrightStatusDate',
      'copyrightJurisdiction',
      'copyrightNote',
      'licenseIdentifier',
      'licenseTerms',
      'licenseNote',
      'statuteJurisdiction',
      'statuteCitation',
      'statuteDeterminationDate',
      'statuteNote');

  protected function addField($name)
  {
    switch ($name)
    {
      case 'endDate':
        $this->form->setDefault('endDate', ($this->right->endDate));
        $this->form->setValidator('endDate', new sfValidatorString);
        $this->form->setWidget('endDate', new sfWidgetFormInput);
        $this->form->getWidgetSchema()->endDate->setLabel($this->context->i18n->__('End'));
        break;

      case 'startDate':
        $this->form->setDefault('startDate', $this->right->startDate);
        $this->form->setValidator('startDate', new sfValidatorString);
        $this->form->setWidget('startDate', new sfWidgetFormInput);
        $this->form->getWidgetSchema()->startDate->setLabel($this->context->i18n->__('Start'));
        break;

      case 'restriction':
        $choices[1] = $this->context->i18n->__('Allow');
        $choices[0] = $this->context->i18n->__('Disallow');
        $this->form->setValidator('restriction', new sfValidatorBoolean);
        $this->form->setWidget('restriction', new sfWidgetFormSelect(array('choices' => $choices)));
        $this->form->setDefault('restriction', $this->right->restriction);
        break;

      case 'statuteDeterminationDate':
      case 'copyrightStatusDate':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);
        $this->form->setDefault($name, $this->right[$name]);
        break;

      case 'act':
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }
        $this->form->setValidator('act', new sfValidatorString);
        $this->form->setWidget('act', new sfWidgetFormSelect(array('choices' => $choices)));
        $this->form->setDefault('act', $this->context->routing->generate(null, array($this->right->act, 'module' => 'term')));
        break;

      case 'basis':
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
        {
          if (QubitTerm::RIGHT_BASIS_POLICY_ID == $item->id)
          {
            $this->form->setDefault('basis', $this->context->routing->generate(null, array($item, 'module' => 'term')));
          }
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }
        $this->form->setValidator('basis', new sfValidatorString);
        $this->form->setWidget('basis', new sfWidgetFormSelect(array('choices' => $choices)));
        $this->form->setDefault('basis', $this->context->routing->generate(null, array($this->right->basis, 'module' => 'term')));
        break;

      case 'copyrightStatus':
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }
        $this->form->setValidator('copyrightStatus', new sfValidatorString);
        $this->form->setWidget('copyrightStatus', new sfWidgetFormSelect(array('choices' => $choices)));
        $this->form->setDefault('copyrightStatus', $this->context->routing->generate(null, array($this->right->copyrightStatus, 'module' => 'term')));
        break;

      case 'rightsHolder':
        $this->form->setValidator('rightsHolder', new sfValidatorString);
        $this->form->setWidget('rightsHolder', new sfWidgetFormSelect(array('choices' => array())));
        // var_dump( $this->context->routing->generate(null, array($this->right->rightsHolder, 'module' => 'actor')) ); die();
        $this->form->setDefault('rightsHolder', $this->context->routing->generate(null, array($this->right->rightsHolder, 'module' => 'actor')));
        break;

      case 'copyrightJurisdiction':
        $this->form->setValidator('copyrightJurisdiction', new sfValidatorI18nChoiceCountry);
        $this->form->setWidget('copyrightJurisdiction', new sfWidgetFormI18nChoiceCountry(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));
        $this->form->setDefault('copyrightJurisdiction', $this->right->copyrightJurisdiction);
        break;

      case 'copyrightNote':
      case 'licenseNote':
      case 'statuteJurisdiction':
      case 'statuteCitation':
      case 'statuteNote':
      case 'rightsNote':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);
        $this->form->setDefault($name, $this->right[$name]);
        break;

      case 'licenseIdentifier':
      case 'licenseTerms':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);
        $this->form->setDefault($name, $this->right[$name]);
        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'act':
      case 'basis':
      case 'copyrightStatus':
      case 'rightsHolder':
        unset($this->right[$field->getName()]);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->right[$field->getName()] = $params['_sf_route']->resource;
        }

        break;

      default:
        $this->right[$field->getName()] = $this->form->getValue($field->getName());
    }
  }

  public function processForm()
  {
    // attach each value in the form
    // to the new/existing rights object
    foreach ($this->form as $field)
    {
      $this->processField($field);
    }

    // in theory we can save the Right now.
    $this->right->save();

    // if new right, then create QubitRelation
    // to associate it to the resource
    if ( $this->right->relationsRelatedByobjectId[0] === null )
    {
      $this->relation = new QubitRelation;
      $this->relation->object = $this->right;
      $this->relation->typeId = QubitTerm::RIGHT_ID;
      $this->relation->subject = $this->resource;
      $this->relation->save();
    }
  }

  protected function getObject($id)
  {
    $results = QubitRelation::getRelatedSubjectsByObjectId('QubitInformationObject', $id);
    return $results[0];
    if( $results !== NULL ){
      return $results[0];
    } else {
      return false;
    }
  }

  protected function newRightWithDefaults()
  {
    $right = new QubitRights;
    $dt = new DateTime;
    $right->startDate = $dt->format('Y-m-d');
    $right->restriction = "0";

    return $right;
  }

  protected function earlyExecute()
  {
    $resource = $this->getRoute()->resource;
    $type = get_class($resource);

    switch ($type) {
      case 'QubitRights':
        $this->resource = $this->getObject($resource->id);
        $this->right = $resource;
        break;
      case 'QubitInformationObject':
        $this->resource = $resource;
        $this->right = $this->newRightWithDefaults();
        break;
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }
  }

  protected function formSetup()
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    if (isset($this->nameFormat))
    {
      $this->form->getWidgetSchema()->setNameFormat($this->nameFormat);
    }
    else
    {
      $this->form->getWidgetSchema()->setNameFormat('editRight[%s]');
    }

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }
  }


  public function execute($request)
  {
    $this->earlyExecute();
    $this->formSetup();

    if ($request->isMethod('post'))
    {
      $params = $request->getPostParameters();
      $this->form->bind($params['editRight']);
      if ($this->form->isValid()){
        $this->processForm();
        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }
}
