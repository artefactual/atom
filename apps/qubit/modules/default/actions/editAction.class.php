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
    //protected $otherNameMap = array();

    public function execute($request)
    {
        // Force subclassing
        if ('default' == $this->context->getModuleName() && 'edit' == $this->context->getActionName()) {
            $this->forward404();
        }

        $this->form = new sfForm();

        // Call early execute logic, if defined by a child class
        if (method_exists($this, 'earlyExecute')) {
            call_user_func([$this, 'earlyExecute']);
        }

        // Mainly used in autocomplete.js, this tells us that the user wants to
        // reuse existing objects instead of adding new ones.
        if (isset($this->request->linkExisting)) {
            $this->form->setDefault('linkExisting', $this->request->linkExisting);
            $this->form->setValidator('linkExisting', new sfValidatorBoolean());
            $this->form->setWidget('linkExisting', new sfWidgetFormInputHidden());
        }

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'descriptionDetail':
                $this->form->setDefault('descriptionDetail', $this->context->routing->generate(null, [$this->resource->descriptionDetail, 'module' => 'term']));
                $this->form->setValidator('descriptionDetail', new sfValidatorString());

                $choices = [];
                $choices[null] = null;
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID) as $item) {
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
                }

                $this->form->setWidget('descriptionDetail', new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'descriptionStatus':
                $this->form->setDefault('descriptionStatus', $this->context->routing->generate(null, [$this->resource->descriptionStatus, 'module' => 'term']));
                $this->form->setValidator('descriptionStatus', new sfValidatorString());

                $choices = [];
                $choices[null] = null;
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_STATUS_ID) as $item) {
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item;
                }

                $this->form->setWidget('descriptionStatus', new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'language':
            case 'languageOfDescription':
                $this->form->setDefault($name, $this->resource[$name]);
                $this->form->setValidator($name, new sfValidatorI18nChoiceLanguage(['multiple' => true]));
                $this->form->setWidget($name, new sfWidgetFormI18nChoiceLanguage(['culture' => $this->context->user->getCulture(), 'multiple' => true]));

                break;

            case 'otherName':
            case 'parallelName':
            case 'standardizedName':
                $criteria = new Criteria();
                $criteria = $this->resource->addOtherNamesCriteria($criteria);

                switch ($name) {
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

                $value = $defaults = [];
                foreach ($this[$name] = QubitOtherName::get($criteria) as $item) {
                    $defaults[$value[] = $item->id] = $item;
                }

                $this->form->setDefault($name, $value);
                $this->form->setValidator($name, new sfValidatorPass());
                $this->form->setWidget($name, new QubitWidgetFormInputMany(['defaults' => $defaults]));

                break;

            case 'script':
            case 'scriptOfDescription':
                $this->form->setDefault($name, $this->resource[$name]);

                $c = sfCultureInfo::getInstance($this->context->user->getCulture());

                $this->form->setValidator($name, new sfValidatorChoice(['choices' => array_keys($c->getScripts()), 'multiple' => true]));
                $this->form->setWidget($name, new sfWidgetFormSelect(['choices' => $c->getScripts(), 'multiple' => true]));

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'descriptionDetail':
            case 'descriptionStatus':
                unset($this->resource[$field->getName()]);

                $value = $this->form->getValue($field->getName());
                if (isset($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $this->resource[$field->getName()] = $params['_sf_route']->resource;
                }

                break;

            case 'otherName':
            case 'parallelName':
            case 'standardizedName':
                $value = $filtered = $this->form->getValue($field->getName());

                //foreach ($this->resource->otherNames as $key => $otherName) {
                //    sfContext::getInstance()->getLogger()->err('SBSBSB Key: ' . $key . ' $otherName Id: ' . $otherName->id);
                //}

                sfContext::getInstance()->getLogger()->err('SBSBSB FIELD NAME: ' . $field->getName());

                foreach ($this[$field->getName()] as $item) {
                    sfContext::getInstance()->getLogger()->err('SBSBSB get_class $item: ' . get_class($item));
                    sfContext::getInstance()->getLogger()->err('SBSBSB $item->id: ' . $item->id);
                    sfContext::getInstance()->getLogger()->err('SBSBSB $item->name: ' . $item->name);
                    sfContext::getInstance()->getLogger()->err('SBSBSB submitted value: ' . $value[$item->id]);
                    

                    // 4 cases in any combination:
                        // some removed / blanked out
                            // item will be deleted from db in 'else' below
                        // some new names added
                            // contained in $filtered. These are created below.
                            // when this happens any other updates are OVERWRITTEN and replaced in othernames.
                            // when new names available, updates do not get saved
                        // some names updated
                            // updates written to resource->othernames
                            // if any new names, resource->othernames is replaced removing any updates.
                        // some names unchanged
                            // these are either left in or taken out depending on whether a new item was added

                    // OtherNames should contain all/only updated/new names:
                        // unchanged - drop from otherNames
                        // deleted - leave in otherNames (delete in QubitObject)
                        // updated - leave in otherNames
                        // new - add to otherNames

                    // In QubitObject names have changed if Othernames is not empty.


                    
                    if (!empty($value[$item->id])) {
                        if ($value[$item->id] !==  $item->name) {
                            sfContext::getInstance()->getLogger()->err('SBSBSB status: ' . 'CHANGED');
                        } else {
                            //sfContext::getInstance()->getLogger()->err('SBSBSB status: ' . 'UNCHANGED' . ' map id: ' . $this->otherNameMap[$item->id]);
                            //unset($this->resource->otherNames[$this->otherNameMap[$item->id]]);
                        }

                        $item->name = $value[$item->id];
                        unset($filtered[$item->id]);
                    } else {
                        sfContext::getInstance()->getLogger()->err('SBSBSB status: ' . 'DELETED');
                        $item->delete();
                    }
                }

                //sfContext::getInstance()->getLogger()->err('SBSBSB filtered: ' . var_export($filtered, true));
                //foreach($this->resource->otherNames as $otherName) {
                    //sfContext::getInstance()->getLogger()->err('SBSBSB othername->name: ' . $otherName->name);
                //}

                sfContext::getInstance()->getLogger()->err('SBSBSB othername COUNT before filtered: ' . count($this->resource->otherNames));

                foreach ($filtered as $item) {
                    if (!$item) {
                        continue;
                    }

                    $otherName = new QubitOtherName();
                    $otherName->name = $item;

                    switch ($field->getName()) {
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
                    sfContext::getInstance()->getLogger()->err('SBSBSB othername COUNT after filtered: ' . count($this->resource->otherNames));
                }

                break;

            default:
                $this->resource[$field->getName()] = $this->form->getValue($field->getName());
        }
    }

    protected function processForm()
    {
        //foreach ($this->resource->otherNames as $key => $otherName) {
        //    sfContext::getInstance()->getLogger()->err('SBSBSB Key: ' . $key . ' $otherName Id: ' . $otherName->id);
        //    $this->otherNameMap[$otherName->id] = $key;
        //}

        foreach ($this->form as $field) {
            $this->processField($field);
        }
    }
}
