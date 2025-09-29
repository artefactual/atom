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
/**
 * Archival Description Feedback edit component.
 *
 * @author     Johan Pieterse <johan@plainsailingisystems.co.za>
 *
 * @version    SVN: $Id
 */
class FeedbackEditFeedbackAction extends DefaultEditAction
{
    public static $NAMES = [
        'feed_name',
        'feed_surname',
        'remarks',
        'identifier',
        'name',
        'feed_phone',
        'unique_identifier',
        'feed_email',
        'feed_relationship',
        'object_id',
        'created_at',
        'completed_at',
        'status_id',
        'cbCompleted',
    ];

    public function execute($request)
    {
        parent::execute($request);
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());
            if ($this->form->isValid()) {
                $this->processForm();
                if (1 != $this->form->getValue('cbCompleted')) {
                    $this->redirect([$this->resource, 'module' => 'feedback', 'action' => 'browse']);
                }
                $this->resource->save();
                $this->redirect([$this->resource, 'module' => 'feedback', 'action' => 'browse']);
            }
        }
    }

    protected function earlyExecute()
    {
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->resource = $this->getRoute()->resource;
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'identifier':
                $informationObj = QubitInformationObject::getById($this->resource->object_id);
                $this->form->setDefault('identifier', $informationObj->identifier); // bring a value of the shelf field in feedback
                $this->form->setValidator('identifier', new sfValidatorString());
                $this->form->setWidget('identifier', new sfWidgetFormInput());

                break;

            case 'unique_identifier':
                $this->form->setDefault('unique_identifier', $this->resource->unique_identifier); // bring a value of the  field in feedback
                $this->form->setValidator('unique_identifier', new sfValidatorString());
                $this->form->setWidget('unique_identifier', new sfWidgetFormInput());

                break;

            case 'remarks':
                $this->form->setDefault('remarks', $this->resource->remarks); // bring a value of the  field in feedback
                $this->form->setValidator('remarks', new sfValidatorString(['required' => true]));
                $this->form->setWidget('remarks', new sfWidgetFormTextArea([], ['rows' => 4]));

                break;

            case 'feed_name':
                $this->form->setDefault('feed_name', $this->resource->feed_name); // bring a value of the  field in feedback
                $this->form->setValidator('feed_name', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_name', new sfWidgetFormInput());

                break;

            case 'feed_surname':
                $this->form->setDefault('feed_surname', $this->resource->feed_surname); // bring a value of the  field in feedback
                $this->form->setValidator('feed_surname', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_surname', new sfWidgetFormInput());

                break;

            case 'feed_phone':
                $this->form->setDefault('feed_phone', $this->resource->feed_phone); // bring a value of the  field in feedback
                $this->form->setValidator('feed_phone', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_phone', new sfWidgetFormInput());

                break;

            case 'feed_email':
                $this->form->setDefault('feed_email', $this->resource->feed_email); // bring a value of the  field in feedback
                $this->form->setValidator('feed_email', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_email', new sfWidgetFormInput());

                break;

            case 'feed_relationship':
                $this->form->setDefault('feed_relationship', $this->resource->feed_relationship); // bring a value of the  field in feedback
                $this->form->setValidator('feed_relationship', new sfValidatorString());
                $this->form->setWidget('feed_relationship', new sfWidgetFormTextArea([], ['rows' => 2]));

                break;

            case 'created_at':
                $this->form->setDefault('createdAt', $this->resource->createdAt); // bring a value of the  field in feedback
                $this->form->setValidator('createdAt', new sfValidatorString());
                $this->form->setWidget('createdAt', new sfWidgetFormInput());

                break;

            case 'completed_at':
                $this->form->setDefault('completedAt', $this->resource->completedAt); // bring a value of the  field in feedback
                $this->form->setValidator('completedAt', new sfValidatorString());
                $this->form->setWidget('completedAt', new sfWidgetFormInput());

                break;

            case 'status_id':
                $this->form->setDefault('statusId', $this->resource->statusId); // bring a value of the  field in feedback
                $this->form->setValidator('statusId', new sfValidatorString());
                $this->form->setWidget('statusId', new sfWidgetFormInput());

                break;

            case 'name':
                $this->form->setDefault($name, $this->resource); // bring a value of a name field in feedback
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            case 'cbCompleted':
                $this->form->setDefault('cbCompleted', false);
                $this->form->setValidator('cbCompleted', new sfValidatorBoolean());
                $this->form->setWidget('cbCompleted', new sfWidgetFormInputCheckbox());

                break;

            default:
                return parent::addField($name);
        }
    }

    protected function processForm()
    {
        if (null !== $this->form->getValue('name')
        || null !== $this->form->getValue('feed_name')
        || null !== $this->form->getValue('feed_surname')
        || null !== $this->form->getValue('feed_phone')
        || null !== $this->form->getValue('feed_email')
        || null !== $this->form->getValue('feed_relationship')) {
            $feedback = $this->resource;

            $feedback->name = $this->form->getValue('name');

            if (null == $this->form->getValue('remarks') || '' == $this->form->getValue('remarks')) {
                $remarks = '';
            } else {
                $remarks = $this->form->getValue('remarks');
            }
            $feedback->remarks = $remarks;

            if (null == $this->form->getValue('feed_name') || '' == $this->form->getValue('feed_name')) {
                $feed_name = '';
            } else {
                $feed_name = $this->form->getValue('feed_name');
            }
            $feedback->feed_name = $feed_name;

            if (null == $this->form->getValue('unique_identifier') || '' == $this->form->getValue('unique_identifier')) {
                $unique_identifier = '';
            } else {
                $unique_identifier = $this->form->getValue('unique_identifier');
            }
            $feedback->parent_id = $unique_identifier; // Unique identifier

            if (null == $this->form->getValue('feed_surname') || '' == $this->form->getValue('feed_surname')) {
                $feed_surname = '';
            } else {
                $feed_surname = $this->form->getValue('feed_surname');
            }
            $feedback->feed_surname = $feed_surname; // new field

            if (null == $this->form->getValue('feed_phone') || '' == $this->form->getValue('feed_phone')) {
                $feed_phone = '';
            } else {
                $feed_phone = $this->form->getValue('feed_phone');
            }
            $feedback->feed_phone = $feed_phone; // new field

            if (null == $this->form->getValue('feed_email') || '' == $this->form->getValue('feed_email')) {
                $feed_email = '';
            } else {
                $feed_email = $this->form->getValue('feed_email');
            }
            $feedback->feed_email = $feed_email;

            if (null == $this->form->getValue('feed_relationship') || '' == $this->form->getValue('feed_relationship')) {
                $feed_relationship = '';
            } else {
                $feed_relationship = $this->form->getValue('feed_relationship');
            }
            $feedback->feed_relationship = $feed_relationship;

            $feedback->completedAt = date('Y-m-d H:i:s');
            $feedback->statusId = QubitTerm::COMPLETED_ID;
            $informationObj = QubitInformationObject::getById($this->resource->id);
            // $feedback->object_id = $informationObj->id;
            $feedback->save();
            $this->feedback = $feedback->id;
            // $this->resource->addFeedback($feedback);
        }

        if (isset($this->request->delete_relations)) {
            foreach ($this->request->delete_relations as $item) {
                $params = $this->context->routing->parse(Qubit::pathInfo($item));
                $params['_sf_route']->resource->delete();
            }
        }
    }
}
