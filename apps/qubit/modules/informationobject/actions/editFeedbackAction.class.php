<?php

// Load PHPMailer manually (since Composer is not used)
require '/usr/share/nginx/phpmailer/src/PHPMailer.php';

require '/usr/share/nginx/phpmailer/src/SMTP.php';

require '/usr/share/nginx/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

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
class InformationObjectEditFeedbackAction extends DefaultEditAction
{
    public static $NAMES = [
        'feed_name',
        'feed_surname',
        'remarks',
        'identifier',
        'name',
        'feed_phone',
        'feed_type',
        'unique_identifier',
        'feed_email',
        'feed_relationship',
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
                $this->resource->save();
                $this->informationObject = QubitInformationObject::getById($this->resource->id);
                $this->redirect([$this->resource, 'module' => 'informationobject']);
            }
        }
    }

    public function sendmail($id, $feed_name, $feed_surname, $feed_phone, $feed_email, $feed_relationship, $feed_remarks, $feed_type)
    {
    $mail = new PHPMailer(true);

    try {
        $host = QubitSetting::getByNameAndScope('host', 'email');
        $port = QubitSetting::getByNameAndScope('port', 'email');
        $smtp_secure = QubitSetting::getByNameAndScope('smtp_secure', 'email');
        $smtp_auth = QubitSetting::getByNameAndScope('smtp_auth', 'email');
        $username = QubitSetting::getByNameAndScope('email_username', 'email');
        $password = QubitSetting::getByNameAndScope('password', 'email');
        $from = QubitSetting::getByNameAndScope('from', 'email');
        $to = QubitSetting::getByNameAndScope('to', 'email');
        $cc = QubitSetting::getByNameAndScope('cc', 'email');
        $reply_to = QubitSetting::getByNameAndScope('reply_to', 'email');
        $subject = QubitSetting::getByNameAndScope('subject', 'email');
        $body = QubitSetting::getByNameAndScope('body', 'email');

        if (0 == $feed_type) {
            $feed_type = 'General';
        } elseif (1 == $feed_type) {
            $feed_type = 'Error';
        } elseif (2 == $feed_type) {
            $feed_type = 'Suggestion';
        } elseif (3 == $feed_type) {
            $feed_type = 'Correction';
        } elseif (4 == $feed_type) {
            $feed_type = 'Need assistance';
        } else {
            $feed_type = 'Other';
        }

        if ('' == $body) {
            $body = "Feedback has been noted:\n".$id."\n Name & surname: ".$feed_name.' '.$feed_surname."\n Telephone: ".$feed_phone."\n Email address: ".$feed_email."\n Relationship: ".$feed_relationship."\n Remarks: ".$feed_remarks."\n Feedback type: ".$feed_type;
        } else {
            $body = "Feedback has been noted:\n".$id."\n Name & surname: ".$feed_name.' '.$feed_surname."\n Telephone: ".$feed_phone."\n Email address: ".$feed_email."\n Relationship: ".$feed_relationship."\n Remarks: ".$feed_remarks."\n Feedback type: ".$feed_type;
        }

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = $host;
        $mail->SMTPAuth = true; // filter_var($smtp_auth, FILTER_VALIDATE_BOOLEAN);
        $mail->Username = $username;  // Your Afrihost email
        $mail->Password = $password; // Your Afrihost email password
        $mail->SMTPSecure = trim(strval($smtp_secure));  // ssl Use 'tls' if using port 587
        $mail->Port = strval($port); // 465

        $mail->From = $feed_email;
        $mail->FromName = $feed_name.' '.$feed_surname; // To address and name
        $mail->addAddress(trim($to), 'Rock Art Research Institute (RARI)'); // Recipient name is optional
        $mail->addAddress('johan@theahg.co.za', 'Johan-AHG'); // Address to which recipient will reply
        $mail->addReplyTo($reply_to, 'Reply'); // CC and BCC
        $mail->addCC($cc);
        $mail->Subject = 'Feedback:'.$id; // $subject;
        $mail->Body = $body;

        // Send email
        $mail->send();
        // echo "Email sent successfully!";
    } catch (Exception $e) {
        echo "Email could not be sent. Error: {$mail->ErrorInfo}";
    }
    }

    protected function earlyExecute()
    {
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->resource = $this->getRoute()->resource;

        // Check that this isn't the root

        if (!isset($this->resource->parent)) {
            $this->forward404();
        }
    }

    protected function addField($name)
    {
        switch ($name) {
            case 'identifier':
                $informationObj = QubitInformationObject::getById($this->resource->id);
                $this->form->setDefault('identifier', $informationObj->identifier); // bring a value of the shelf field in feedback
                $this->form->setValidator('identifier', new sfValidatorString());
                $this->form->setWidget('identifier', new sfWidgetFormInput());

                break;

            case 'unique_identifier':
                $informationObj = QubitInformationObject::getById($this->resource->id);
                foreach ($informationObj->getPhysicalObjects() as $item) {
                    $this->form->setDefault('unique_identifier', $item->uniqueIdentifier); // bring a value of the  field in feedback
                }
                $this->form->setValidator('unique_identifier', new sfValidatorString());
                $this->form->setWidget('unique_identifier', new sfWidgetFormInput());

                break;

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
                $this->form->setValidator('feed_name', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_name', new sfWidgetFormInput());

                break;

            case 'feed_surname':
                $this->form->setDefault('feed_surname', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_surname', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_surname', new sfWidgetFormInput());

                break;

            case 'feed_phone':
                $this->form->setDefault('feed_phone', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_phone', new sfValidatorString());
                $this->form->setWidget('feed_phone', new sfWidgetFormInput());

                break;

            case 'feed_email':
                $this->form->setDefault('feed_email', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_email', new sfValidatorString(['required' => true]));
                $this->form->setWidget('feed_email', new sfWidgetFormInput());

                break;

            case 'feed_relationship':
                $this->form->setDefault('feed_relationship', ''); // bring a value of the  field in feedback
                $this->form->setValidator('feed_relationship', new sfValidatorString());
                $this->form->setWidget('feed_relationship', new sfWidgetFormTextArea([], ['rows' => 2]));

                break;

            case 'name':
                $this->form->setDefault($name, $this->resource); // bring a value of a name field in feedback
                $this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, new sfWidgetFormInput());

                break;

            default:
                return parent::addField($name);
        }
    }

    protected function processForm()
    {
        if (null !== $this->form->getValue('name')
        || null !== $this->form->getValue('unique_identifier')
        || null !== $this->form->getValue('feed_name')
        || null !== $this->form->getValue('feed_surname')
        || null !== $this->form->getValue('feed_phone')
        || null !== $this->form->getValue('feed_email')
        || null !== $this->form->getValue('feed_relationship')) {
            $feedback = new QubitFeedback();

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

            if (null == $this->form->getValue('feed_type') || '' == $this->form->getValue('feed_type')) {
                $feed_type = 0;
            } else {
                $feed_type = $this->form->getValue('feed_type');
            }
            $feedback->feedTypeId = $feed_type;

            $feedback->createdAt = date('Y-m-d H:i:s');
            $feedback->statusId = QubitTerm::PENDING_ID;
            $informationObj = QubitInformationObject::getById($this->resource->id);
            $feedback->object_id = $informationObj->id;
            $feedback->save();
            $this->feedback = $feedback->id;
            $this->resource->addFeedback($feedback);

            // send email
            $this->sendmail($this->informationObj, $feed_name, $feed_surname, $feed_phone, $feed_email, $feed_relationship, $remarks, $feed_type);
        }

        if (isset($this->request->delete_relations)) {
            foreach ($this->request->delete_relations as $item) {
                $params = $this->context->routing->parse(Qubit::pathInfo($item));
                $params['_sf_route']->resource->delete();
            }
        }
    }
}
