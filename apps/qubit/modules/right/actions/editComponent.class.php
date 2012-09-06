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

class RightEditComponent extends sfComponent
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
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
    if (isset($this->nameFormat))
    {
      $name = preg_replace('/\[.*\]/', 's', $this->nameFormat);
      $params = $this->request[$name];
    }
    else
    {
      $params = $this->request->editRights;
    }

    foreach ((array)$params as $item)
    {
      // Continue only if user typed something
      foreach ($item as $value)
      {
        if (0 < strlen($value))
        {
          break;
        }
      }

      if (1 > strlen($value))
      {
        continue;
      }

      $this->form->bind($item);
      if ($this->form->isValid())
      {
        if (isset($item['id']))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($item['id']));
          $this->right = $params['_sf_route']->resource;
        }
        else
        {
          $this->right = new QubitRights;
        }

        foreach ($this->form as $field)
        {
          if (isset($item[$field->getName()]))
          {
            $this->processField($field);
          }
        }

        $this->right->save();

        if (!isset($item['id']))
        {
          $this->relation = new QubitRelation;
          $this->relation->object = $this->right;
          $this->relation->typeId = QubitTerm::RIGHT_ID;

          $this->resource->relationsRelatedBysubjectId[] = $this->relation;
        }
      }
    }

    // Stop here if duplicating
    if (isset($this->request->sourceId))
    {
      return;
    }

    if (isset($this->nameFormat))
    {
      preg_match('/\d+/', $this->nameFormat, $usageId);
      $deleteRights = $this->request['deleteRights_'.$usageId[0]];
    }
    else
    {
      $deleteRights = $this->request->deleteRights;
    }

    if (isset($deleteRights))
    {
      foreach ($deleteRights as $item)
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($item));
        $params['_sf_route']->resource->delete();
      }
    }
  }

  public function execute($request)
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

    $this->rights = QubitRelation::getRelationsBySubjectId($this->resource->id, array('typeId' => QubitTerm::RIGHT_ID));
  }
}
