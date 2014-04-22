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
      case 'id':
        $this->form->setValidator('id', new sfValidatorString);
        $this->form->setWidget('id', new sfWidgetFormInputHidden);
        $this->form->setDefault('id', $this->resource->id);
        break;

      case 'endDate':
        $this->form->setValidator('endDate', new sfValidatorString);
        $this->form->setWidget('endDate', new sfWidgetFormInput);
        $this->form->getWidgetSchema()->endDate->setLabel($this->context->i18n->__('End'));
        break;

      case 'startDate':
        $dt = new DateTime;
        $this->form->setDefault('startDate', $dt->format('Y-m-d'));
        $this->form->setValidator('startDate', new sfValidatorString);
        $this->form->setWidget('startDate', new sfWidgetFormInput);
        $this->form->getWidgetSchema()->startDate->setLabel($this->context->i18n->__('Start'));
        break;

      case 'restriction':
        $choices[1] = $this->context->i18n->__('Allow');
        $choices[0] = $this->context->i18n->__('Disallow');
        $this->form->setValidator('restriction', new sfValidatorBoolean);
        $this->form->setWidget('restriction', new sfWidgetFormSelect(array('choices' => $choices)));
        break;

      case 'statuteDeterminationDate':
      case 'copyrightStatusDate':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);
        break;

      case 'act':
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }
        $this->form->setValidator('act', new sfValidatorString);
        $this->form->setWidget('act', new sfWidgetFormSelect(array('choices' => $choices)));
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
        break;

      case 'copyrightStatus':
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item->__toString();
        }
        $this->form->setValidator('copyrightStatus', new sfValidatorString);
        $this->form->setWidget('copyrightStatus', new sfWidgetFormSelect(array('choices' => $choices)));
        break;

      case 'rightsHolder':
        $this->form->setValidator('rightsHolder', new sfValidatorString);
        $this->form->setWidget('rightsHolder', new sfWidgetFormSelect(array('choices' => array())));
        break;

      case 'copyrightJurisdiction':
        $this->form->setValidator('copyrightJurisdiction', new sfValidatorI18nChoiceCountry);
        $this->form->setWidget('copyrightJurisdiction', new sfWidgetFormI18nChoiceCountry(array('add_empty' => true, 'culture' => $this->context->user->getCulture())));
        break;

      case 'copyrightNote':
      case 'licenseNote':
      case 'statuteJurisdiction':
      case 'statuteCitation':
      case 'statuteNote':
      case 'rightsNote':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormTextarea);
        break;

      case 'licenseIdentifier':
      case 'licenseTerms':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);
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
    // load existing right, or create a new one
    $this->right = new QubitRights;

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
    if (!$this->form->getValue('id'))
    {
      $this->relation = new QubitRelation;
      $this->relation->object = $this->right;
      $this->relation->typeId = QubitTerm::RIGHT_ID;
      $this->relation->subject = $this->resource;
      $this->relation->save();
    }
  }

  protected function earlyExecute()
  {
    $this->resource = $this->getRoute()->resource;

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
