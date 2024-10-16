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

class RightEditAction extends sfAction
{
    // Arrays not allowed in class constants
    public static $NAMES = [
        'basis',
        'endDate',
        'startDate',
        'rightsHolder',
        'rightsNote',
        'copyrightStatus',
        'copyrightStatusDate',
        'copyrightJurisdiction',
        'copyrightNote',
        'identifierType',
        'identifierValue',
        'identifierRole',
        'licenseTerms',
        'licenseNote',
        'statuteJurisdiction',
        'statuteCitation',
        'statuteDeterminationDate',
        'statuteNote',
    ];

    public function execute($request)
    {
        $this->earlyExecute();
        $this->formSetup();

        if ($request->isMethod('post')) {
            $params = $request->getPostParameters();
            $this->form->bind($params['right']);
            if ($this->form->isValid()) {
                $this->processForm();
                $this->redirect($this->redirectTo);
            }
        }
    }

    protected function dateWidget()
    {
        $widget = new sfWidgetFormInput();
        $widget->setAttribute('type', 'date');

        return $widget;
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'endDate':
                $this->form->setDefault('endDate', $this->right->endDate);
                $this->form->setValidator('endDate', new sfValidatorString());
                $this->form->setWidget('endDate', $this->dateWidget());
                $this->form->getWidgetSchema()->endDate->setLabel($this->context->i18n->__('End'));

                break;

            case 'startDate':
                $this->form->setDefault('startDate', $this->right->startDate);
                $this->form->setValidator('startDate', new sfValidatorString());
                $this->form->setWidget('startDate', $this->dateWidget());
                $this->form->getWidgetSchema()->startDate->setLabel($this->context->i18n->__('Start'));

                break;

            case 'statuteDeterminationDate':
            case 'copyrightStatusDate':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, $this->dateWidget());
                $this->form->setDefault($name, $this->right[$name]);
                if ('copyrightStatusDate' == $name) {
                    $this->form->getWidgetSchema()->{$name}->setLabel($this->context->i18n->__('Copyright status determination date'));
                }

                break;

            case 'basis':
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item) {
                    if (QubitTerm::RIGHT_BASIS_POLICY_ID == $item->id) {
                        $this->form->setDefault('basis', $this->context->routing->generate(null, [$item, 'module' => 'term']));
                    }
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item->__toString();
                }
                $this->form->setValidator('basis', new sfValidatorString());
                $this->form->setWidget('basis', new sfWidgetFormSelect(['choices' => $choices]));
                $this->form->setDefault('basis', $this->context->routing->generate(null, [$this->right->basis, 'module' => 'term']));

                break;

            case 'copyrightStatus':
                foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item) {
                    $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item->__toString();
                }

                $this->form->setValidator('copyrightStatus', new sfValidatorString());
                $this->form->setWidget('copyrightStatus', new sfWidgetFormSelect(['choices' => $choices]));
                $this->form->setDefault('copyrightStatus', $this->context->routing->generate(null, [$this->right->copyrightStatus, 'module' => 'term']));

                break;

            case 'rightsHolder':
                $choices = [];
                if ($this->right->rightsHolder) {
                    $choices[$this->context->routing->generate(null, [$this->right->rightsHolder, 'module' => 'actor'])] = $this->right->rightsHolder->__toString();
                }
                $this->form->setValidator('rightsHolder', new sfValidatorString());
                $this->form->setWidget('rightsHolder', new sfWidgetFormSelect(['choices' => $choices]));
                $this->form->setDefault('rightsHolder', $this->context->routing->generate(null, [$this->right->rightsHolder, 'module' => 'actor']));

                break;

            case 'copyrightJurisdiction':
                $this->form->setValidator('copyrightJurisdiction', new sfValidatorI18nChoiceCountry());
                $this->form->setWidget('copyrightJurisdiction', new sfWidgetFormI18nChoiceCountry(['add_empty' => true, 'culture' => $this->context->user->getCulture()]));
                $this->form->setDefault('copyrightJurisdiction', $this->right->copyrightJurisdiction);

                break;

            case 'copyrightNote':
            case 'licenseNote':
            case 'statuteJurisdiction':
            case 'statuteNote':
            case 'rightsNote':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormTextarea());
                $this->form->setDefault($name, $this->right[$name]);

                break;

            case 'statuteCitation':
                $this->form->setDefault('statuteCitation', $this->context->routing->generate(null, [$this->right->statuteCitation, 'module' => 'term']));
                $this->form->setValidator('statuteCitation', new sfValidatorString());

                $choices = [];
                if (isset($this->right->statuteCitation)) {
                    $choices[$this->context->routing->generate(null, [$this->right->statuteCitation, 'module' => 'term'])] = $this->right->statuteCitation;
                }

                $this->form->setWidget('statuteCitation', new sfWidgetFormSelect(['choices' => $choices]));

                break;

            case 'identifierType':
            case 'identifierValue':
            case 'identifierRole':
            case 'licenseTerms':
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());
                $this->form->setDefault($name, $this->right[$name]);

                break;
        }
    }

    protected function processField($field)
    {
        switch ($field->getName()) {
            case 'basis':
            case 'copyrightStatus':
            case 'rightsHolder':
            case 'statuteCitation':
                unset($this->right[$field->getName()]);

                $value = $this->form->getValue($field->getName());
                if (isset($value)) {
                    $params = $this->context->routing->parse(Qubit::pathInfo($value));
                    $this->right[$field->getName()] = $params['_sf_route']->resource;
                }

                break;

            case 'grantedRights':
                foreach ($field->getValue() as $data) {
                    $grantedRight = null;

                    // try and find pre-existing record with this id
                    $grantedRight = $this->right->grantedRightsFindById($data['id']);

                    // if one was found, but user
                    // has requested it be deleted
                    // then lets delete it.
                    if (null !== $grantedRight && 'true' === $data['delete']) {
                        $grantedRight->delete();

                        continue;
                    }

                    // none found, so make a new one
                    if (false === $grantedRight) {
                        $grantedRight = new QubitGrantedRight();
                    }

                    $actparams = $this->context->routing->parse(Qubit::pathInfo($data['act']));
                    $grantedRight->act = $actparams['_sf_route']->resource;
                    $grantedRight->restriction = $data['restriction'];

                    // empty dates come in as empty strings, but propel wants 'null' or it'll default to today's date
                    $grantedRight->startDate = strlen($data['startDate'] > 0) ? $data['startDate'] : null;
                    $grantedRight->endDate = strlen($data['endDate'] > 0) ? $data['endDate'] : null;
                    $grantedRight->notes = $data['notes'];

                    // relate it to the Right if it is new
                    if (null === $grantedRight->id) {
                        $this->right->grantedRights[] = $grantedRight;
                    }
                }

                // no break
            case 'blank':
                break;

            default:
                $this->right[$field->getName()] = $this->form->getValue($field->getName());
        }
    }

    protected function processForm()
    {
        // attach each value in the form
        // to the new/existing rights object
        foreach ($this->form as $field) {
            $this->processField($field);
        }

        // in theory we can save the Right now.
        $this->right->save();

        // if new right, then create QubitRelation
        // to associate it to the resource
        if (null === $this->right->relationsRelatedByobjectId[0]) {
            $this->relation = new QubitRelation();
            $this->relation->object = $this->right;
            $this->relation->typeId = QubitTerm::RIGHT_ID;
            $this->relation->subject = $this->resource;

            $this->relation->save();
        }
    }

    protected function getRelatedObject($id)
    {
        $results = QubitRelation::getRelatedSubjectsByObjectId(
            'QubitInformationObject',
            $id,
            ['typeId' => QubitTerm::RIGHT_ID]
        );

        if (0 < count($results)) {
            return $results[0];
        }

        $results = QubitRelation::getRelatedSubjectsByObjectId(
            'QubitDigitalObject',
            $id,
            ['typeId' => QubitTerm::RIGHT_ID]
        );

        if (0 < count($results)) {
            return $results[0];
        }

        $results = QubitRelation::getRelatedSubjectsByObjectId(
            'QubitAccession',
            $id,
            ['typeId' => QubitTerm::RIGHT_ID]
        );

        if (0 < count($results)) {
            return $results[0];
        }

        return null;
    }

    protected function newRightWithDefaults()
    {
        $right = new QubitRights();
        $dt = new DateTime();
        $right->startDate = $dt->format('Y-m-d');
        $right->restriction = '0';

        return $right;
    }

    protected function setRedirect($type)
    {
        switch ($type) {
            case 'QubitInformationObject':
            case 'QubitDigitalObject':
                $this->redirectTo = [$this->informationObject, 'module' => 'informationObject'];

                break;

            case 'QubitAccession':
                $this->redirectTo = [$this->resource, 'module' => 'accession'];

                break;
        }
    }

    protected function earlyExecute()
    {
        $object = $this->getRoute()->resource;
        $type = get_class($object);

        // editing an existing QubitRights - need to determine if the rights
        // are associated to InformationObject, Accession or DigitalObject
        if ('QubitRights' === $type) {
            $this->resource = $this->getRelatedObject($object->id);
            $type = get_class($this->resource);
            $this->right = $object;
        }
        // we're creating new rights object on the object provided
        else {
            $this->resource = $object;
            $this->right = $this->newRightWithDefaults();
        }

        // need the informationObject handy for redirects and ACL checks
        if ('QubitDigitalObject' == $type) {
            if (isset($this->resource->parent)) {
                $this->informationObject = $this->resource->parent->informationObject;
            } else {
                $this->informationObject = $this->resource->informationObject;
            }
        } else {
            $this->informationObject = $this->resource;
        }

        // set where we redirect to after processing the new/changed right
        $this->setRedirect($type);

        // if we haven't got a resource, we have a problem houston
        if (null === $this->resource) {
            $this->forward404();
        }

        // Check that this isn't the root
        if ('QubitInformationObject' == $type && !isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->informationObject, 'update') && !$this->getUser()->hasGroup(QubitAclGroup::EDITOR_ID)) {
            QubitAcl::forwardUnauthorized();
        }
    }

    protected function grantedRightFormSetup($grantedRight)
    {
        // if new, unsaved right, then id can be set to 0
        if (null === $grantedRight->id) {
            $grantedRight->id = 0;
        }

        // 'id', 'act', 'startDate', 'endDate', 'restriction', 'notes'
        $form = new sfForm();
        $form->getValidatorSchema()->setOption('allow_extra_fields', true);
        $form->getWidgetSchema()->setNameFormat('grantedRight[%s][]');

        $form->setValidator('id', new sfValidatorInteger());
        $form->setWidget('id', new sfWidgetFormInputHidden());
        $form->setDefault('id', $grantedRight->id);

        $form->setValidator('delete', new sfValidatorString());
        $form->setWidget('delete', new sfWidgetFormInputHidden());

        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_ACT_ID) as $item) {
            $choices[$this->context->routing->generate(null, [$item, 'module' => 'term'])] = $item->__toString();
        }

        $form->setValidator('act', new sfValidatorString());
        $form->setWidget('act', new sfWidgetFormSelect(['choices' => $choices]));
        $form->setDefault('act', $this->context->routing->generate(null, [$grantedRight->act, 'module' => 'term']));

        $form->setValidator('startDate', new sfValidatorString());
        $form->setWidget('startDate', $this->dateWidget());
        $form->getWidgetSchema()->startDate->setLabel($this->context->i18n->__('Start'));
        $form->setDefault('startDate', $grantedRight->startDate);

        $form->setValidator('endDate', new sfValidatorString());
        $form->setWidget('endDate', $this->dateWidget());
        $form->getWidgetSchema()->endDate->setLabel($this->context->i18n->__('End'));
        $form->setDefault('endDate', $grantedRight->endDate);

        $res_choices[1] = $this->context->i18n->__('Allow');
        $res_choices[2] = $this->context->i18n->__('Conditional');
        $res_choices[0] = $this->context->i18n->__('Disallow');
        $form->setValidator('restriction', new sfValidatorInteger());
        $form->setWidget('restriction', new sfWidgetFormSelect(['choices' => $res_choices]));
        $form->setDefault('restriction', $grantedRight->restriction);

        $form->setValidator('notes', new sfValidatorString());
        $form->setWidget('notes', new sfWidgetFormTextarea());
        $form->setDefault('notes', $grantedRight->notes);

        return $form;
    }

    protected function formSetup()
    {
        $this->form = new sfForm();
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->form->getWidgetSchema()->setNameFormat('right[%s]');

        foreach ($this::$NAMES as $name) {
            $this->addField($name);
        }

        // handle related act records:
        // if new rights, generate one empty act by default
        // if existing rights, look at related act records
        // and generate an act row for each one.
        $subForm = new sfForm();
        $subForm->getValidatorSchema()->setOption('allow_extra_fields', true);
        if (0 < count($this->right->grantedRights)) {
            foreach ($this->right->grantedRights as $i => $gr) {
                $subForm->embedForm($i, $this->grantedRightFormSetup($gr));
            }
        } else {
            $subForm->embedForm(0, $this->grantedRightFormSetup(new QubitGrantedRight()));
        }

        // finally store a blank GrantedRight form that'll be used
        // by javascript as a reference for adding new grantedRights
        // forms
        $gr = new QubitGrantedRight();
        $this->form->embedForm('blank', $this->grantedRightFormSetup($gr));
        $this->form->embedForm('grantedRights', $subForm);
    }
}
