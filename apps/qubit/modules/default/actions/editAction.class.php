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

class DefaultEditAction extends sfAction
{
  protected function addField($name)
  {
    switch ($name)
    {
      case 'descriptionDetail':
        $this->form->setDefault('descriptionDetail', $this->context->routing->generate(null, array($this->resource->descriptionDetail, 'module' => 'term')));
        $this->form->setValidator('descriptionDetail', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('descriptionDetail', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'descriptionStatus':
        $this->form->setDefault('descriptionStatus', $this->context->routing->generate(null, array($this->resource->descriptionStatus, 'module' => 'term')));
        $this->form->setValidator('descriptionStatus', new sfValidatorString);

        $choices = array();
        $choices[null] = null;
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_STATUS_ID) as $item)
        {
          $choices[$this->context->routing->generate(null, array($item, 'module' => 'term'))] = $item;
        }

        $this->form->setWidget('descriptionStatus', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'language':
      case 'languageOfDescription':
        $this->form->setDefault($name, $this->resource[$name]);
        $this->form->setValidator($name, new sfValidatorI18nChoiceLanguage(array('multiple' => true)));
        $this->form->setWidget($name, new sfWidgetFormI18nChoiceLanguage(array('culture' => $this->context->user->getCulture(), 'multiple' => true)));

        break;

      case 'otherName':
      case 'parallelName':
      case 'standardizedName':
        $criteria = new Criteria;
        $criteria = $this->resource->addOtherNamesCriteria($criteria);
        switch ($name)
        {
          case 'otherName':
            $criteria->add(QubitOtherName::TYPE_ID, QubitTerm::OTHER_FORM_OF_NAME_ID);

            break;

          case 'parallelName':
            $criteria->add(QubitOtherName::TYPE_ID, QubitTerm::PARALLEL_FORM_OF_NAME_ID);

            break;

          case 'standardizedName':
            $criteria->add(QubitOtherName::TYPE_ID, QubitTerm::STANDARDIZED_FORM_OF_NAME_ID);

            break;
        }

        $value = $defaults = array();
        foreach ($this[$name] = QubitOtherName::get($criteria) as $item)
        {
          $defaults[$value[] = $item->id] = $item;
        }

        $this->form->setDefault($name, $value);
        $this->form->setValidator($name, new sfValidatorPass);
        $this->form->setWidget($name, new QubitWidgetFormInputMany(array('defaults' => $defaults)));

        break;

      case 'script':
      case 'scriptOfDescription':
        $this->form->setDefault($name, $this->resource[$name]);

        $c = sfCultureInfo::getInstance($this->context->user->getCulture());

        $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($c->getScripts()), 'multiple' => true)));
        $this->form->setWidget($name, new sfWidgetFormSelect(array('choices' => $c->getScripts(), 'multiple' => true)));

        break;
    }
  }

  protected function processField($field)
  {
    switch ($field->getName())
    {
      case 'descriptionDetail':
      case 'descriptionStatus':
        unset($this->resource[$field->getName()]);

        $value = $this->form->getValue($field->getName());
        if (isset($value))
        {
          $params = $this->context->routing->parse(Qubit::pathInfo($value));
          $this->resource[$field->getName()] = $params['_sf_route']->resource;
        }

        break;

      case 'otherName':
      case 'parallelName':
      case 'standardizedName':
        $value = $filtered = $this->form->getValue($field->getName());

        foreach ($this[$field->getName()] as $item)
        {
          if (!empty($value[$item->id]))
          {
            $item->name = $value[$item->id];
            unset($filtered[$item->id]);
          }
          else
          {
            $item->delete();
          }
        }

        if (is_array($filtered))
        {
          foreach ($filtered as $item)
          {
            if (!$item)
            {
              continue;
            }

            $otherName = new QubitOtherName;
            $otherName->name = $item;

            switch ($field->getName())
            {
              case 'parallelName':
                $otherName->typeId = QubitTerm::PARALLEL_FORM_OF_NAME_ID;

                break;

              case 'standardizedName':
                $otherName->typeId = QubitTerm::STANDARDIZED_FORM_OF_NAME_ID;

                break;

              default:
                $otherName->typeId = QubitTerm::OTHER_FORM_OF_NAME_ID;
            }

            $this->resource->otherNames[] = $otherName;
          }
        }

        break;

      default:
        $this->resource[$field->getName()] = $this->form->getValue($field->getName());
    }
  }

  protected function processForm()
  {
    foreach ($this->form as $field)
    {
      $this->processField($field);
    }
  }

  public function execute($request)
  {
    // Force subclassing
    if ('default' == $this->context->getModuleName() && 'edit' == $this->context->getActionName())
    {
      $this->forward404();
    }

    $this->form = new sfForm;

    // Call early execute logic, if defined by a child class
    if (method_exists($this, 'earlyExecute'))
    {
      call_user_func(array($this, 'earlyExecute'));
    }

    // Mainly used in autocomplete.js, this tells us that the user wants to
    // reuse existing objects instead of adding new ones.
    if (isset($this->request->linkExisting))
    {
      $this->form->setDefault('linkExisting', $this->request->linkExisting);
      $this->form->setValidator('linkExisting', new sfValidatorBoolean);
      $this->form->setWidget('linkExisting', new sfWidgetFormInputHidden);
    }

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }
  }
}
