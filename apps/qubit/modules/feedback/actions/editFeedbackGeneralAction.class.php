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
 * Physical Object edit component.
 *
 * @author     Johan Pieterse <johan.pieterse@sita.co.za>
 *
 * @version    SVN: $Id
 */
class FeedbackEditFeedbackGeneralAction extends DefaultEditAction
{
    public static $NAMES = [
        'feed_name',
        'feed_surname',
        'remarks',
        'feed_phone',
        'feed_email',
        'feed_relationship',
        'feed_type',
        'object_id',
        'created_at',
        'cbReceipt',
    ];

    public function execute($request)
    {
        parent::execute($request);
        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());
            if ($this->form->isValid()) {
                $this->processForm();
                $config['base_url'] = ((isset($_SERVER['HTTPS']) && 'on' == $_SERVER['HTTPS']) ? 'https' : 'http');
                $config['base_url'] .= '://'.$_SERVER['HTTP_HOST'];
                $config['base_url'] .= str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
                // $this->redirect($config['base_url']);
            }
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'feed_type':
                $this->form->setDefault('feed_type', '');
                $this->form->setValidator('feed_type', new sfValidatorString());
                $this->form->setWidget('feed_type', new sfWidgetFormSelect(['choices' => [0 => 'General', 1 => 'Error', 2 => 'Suggestion', 3 => 'Correction', 4 => 'Need assistance']]));
                // $this->form->setWidget("feed_type", new sfWidgetFormSelect(["choices" => QubitTerm::getIndentedChildTree(QubitTerm::FEED_TYPE_ID, "&nbsp;", ["returnObjectInstances" => true]),]));

                break;

            case 'remarks':
                $this->form->setDefault('remarks', ''); // bring a value of the  field in feedback
                $this->form->setValidator('remarks', new sfValidatorString(['required' => true]));
                $this->form->setWidget('remarks', new sfWidgetFormTextArea([], ['rows' => 4]));

                break;

            case 'feed_name':
                $this->form->setDefault('feed_name', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_name', new sfValidatorString());
                $this->form->setWidget('feed_name', new sfWidgetFormInput());

                break;

            case 'feed_surname':
                $this->form->setDefault('feed_surname', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_surname', new sfValidatorString());
                $this->form->setWidget('feed_surname', new sfWidgetFormInput());

                break;

            case 'feed_phone':
                $this->form->setDefault('feed_phone', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_phone', new sfValidatorString());
                $this->form->setWidget('feed_phone', new sfWidgetFormInput());

                break;

            case 'feed_email':
                $this->form->setDefault('feed_email', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_email', new sfValidatorEmail());
                $this->form->setWidget('feed_email', new sfWidgetFormInput());

                break;

            case 'feed_relationship':
                $this->form->setDefault('feed_relationship', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_relationship', new sfValidatorString());
                $this->form->setWidget('feed_relationship', new sfWidgetFormTextArea([], ['rows' => 2]));

                break;

            default:
                return parent::addField($name);
        }
    }

    protected function processForm()
    {
        if (null !== $this->form->getValue('feed_name')
        || null !== $this->form->getValue('feed_surname')
        || null !== $this->form->getValue('feed_phone')
        || null !== $this->form->getValue('feed_email')
        || null !== $this->form->getValue('feed_relationship')) {
            $feedback = new QubitFeedback();

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

            if (null == $this->form->getValue('feed_type') || '' == $this->form->getValue('feed_type')) {
                $feed_type = 0;
            } else {
                $feed_type = $this->form->getValue('feed_type');
            }
            $feedback->feedTypeId = $feed_type;

            $feedback->createdAt = date('Y-m-d H:i:s');
            $feedback->statusId = QubitTerm::PENDING_ID;
            $feedback->name = 'General Feedback';
            $feedback->save();
        }
    }
}
