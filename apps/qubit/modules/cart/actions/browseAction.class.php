<?php

/**
 * Cart List component.
 *
 * @package    qubit
 * @subpackage Cart List Module
 * @author     Johan Pieterse <johan@plainsailingisystems.co.za>
 * @version    SVN: $Id 
 */
require '/usr/share/nginx/phpmailer/src/PHPMailer.php';
require '/usr/share/nginx/phpmailer/src/SMTP.php';
require '/usr/share/nginx/phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
 
class CartBrowseAction extends sfAction
{
	
	public static $NAMES = array(
		'unique_identifier', 
		'rtp_name', 
		'rtp_surname', 
		'rtp_phone', 
		'rtp_email', 
		'rtp_institution', 
		'rtp_motivation', 
		'rtp_planned_use',
		'rtp_need_image_by', 
		'object_id', 
		'created_at', 
		'cbReceipt'
	);
	
	public function execute($request)
	{
        $this->form = new sfForm([], [], false);
        $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

		// Check that this isn't the root
		if (!isset($this->resource->parent)) {
			//$this->forward404();
		}

		foreach ($this::$NAMES as $name) {
            $this->addField($name, $request);
        }

		$title = "Cart"; //$this->context->i18n->__('Cart');
		//$this->response->setTitle("{$title} - {$this->response->getTitle()}");

		if (!isset($request->limit)) {
			$request->limit = sfConfig::get('app_hits_per_page');
		}

		if (sfConfig::get('app_enable_institutional_scoping')) {
			//remove search-realm
			$this->context->user->removeAttribute('search-realm');
		}

		if (!$this->getUser()->isAuthenticated())
		{
		  QubitAcl::forwardUnauthorized();
		}

		if (!isset($request->limit))
		{
		  $request->limit = sfConfig::get('app_hits_per_page');
		}


		$criteria = new Criteria;
		$criteria->add(QubitCart::USER_ID, $this->context->user->getAttribute('user_id'));

		BaseCart::addSelectColumns($criteria);

		switch ($request->sort)
		{
		  case 'idDown':
			$criteria->addDescendingOrderByColumn('USER_ID');

			break;

		  case 'adDown':
			$criteria->addDescendingOrderByColumn('ARCHIVAL_DESCRIPTION');

			break;

		  case 'idUp':
			$criteria->addAscendingOrderByColumn('ARCHIVAL_DESCRIPTION');

			break;

		  default:
			$request->sort = 'idUp';
			$criteria->addAscendingOrderByColumn('USER_ID');
		}

		// Page results
		$this->pager = new QubitPager('QubitCart');
		$this->pager->setCriteria($criteria);
		$this->pager->setMaxPerPage($request->limit);
		$this->pager->setPage($request->page);

		if ($request->isMethod('post')) {
			$this->form->bind($request->getPostParameters());
			if ($this->form->isValid()) {
			
				foreach ($this->pager->getResults() as $item) {
					$this->processForm($item->archivalDescriptionId, $item->id);
				}
				
				//$this->informationObject = QubitInformationObject::getById($this->resource->id);
				//$this->redirect(array($this->resource, 'module' => 'informationobject'));
			}
		}
	}

	protected function addField($name) {
		$user = QubitUser::getById($this->context->user->getAttribute('user_id')); 
		
		switch ($name) {
			case 'unique_identifier':
				$this->form->setDefault('unique_identifier', $user->id); 
				$this->form->setValidator('unique_identifier', new sfValidatorString);
				$this->form->setWidget('unique_identifier', new sfWidgetFormInput);
				break;
			
			case 'rtp_name':
				if ($user) {
					$this->form->setDefault('rtp_name', ""); 
				} else {
					$this->form->setDefault('rtp_name', ""); 
				}

				$this->form->setValidator('rtp_name', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_name', new sfWidgetFormInput);
				break;
			
			case 'rtp_surname':
				if ($user) {
					$this->form->setDefault('rtp_surname', $user->username); 
				} else {
					$this->form->setDefault('rtp_surname', ""); 
				}
				$this->form->setValidator('rtp_surname', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_surname', new sfWidgetFormInput);
				break;
			
			case 'rtp_phone':
				if ($user) {
					$this->form->setDefault('rtp_phone', $user->username); 
				} else {
					$this->form->setDefault('rtp_phone', ""); 
				}
				$this->form->setDefault('rtp_phone', ""); 
				$this->form->setValidator('rtp_phone', new sfValidatorString(['required' => true])); 
				$this->form->setWidget('rtp_phone', new sfWidgetFormInput);
				break;
			
			case 'rtp_email':
				if ($user) {
					$this->form->setDefault('rtp_email', $user->email); 
				} else {
					$this->form->setDefault('rtp_email', ""); 
				}
				$this->form->setDefault('rtp_email', ""); 
				$this->form->setValidator('rtp_email', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_email', new sfWidgetFormInput);
				break;
			
			case 'rtp_institution':
				if ($user) {
					$this->form->setDefault('rtp_email', $user->email); 
				} else {
					$this->form->setDefault('rtp_email', ""); 
				}
				$this->form->setDefault('rtp_institution', ""); 
				$this->form->setValidator('rtp_institution', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_institution', new sfWidgetFormInput);
				break;
			
			case 'rtp_planned_use':
				$this->form->setDefault($name, ""); 
				$this->form->setValidator($name, new sfValidatorString(['required' => true]));
				$this->form->setWidget($name, new sfWidgetFormInput);
				break;

			case 'rtp_need_image_by':
				$this->form->setDefault($name, ""); 
				$this->form->setValidator($name, new sfValidatorString());
                $this->form->setWidget($name, $this->dateWidget());
                $this->form->getWidgetSchema()->rtp_need_image_by->setLabel($this->context->i18n->__('rtp_need_image_by'));
				break;

			case 'rtp_motivation':
				$this->form->setDefault('rtp_motivation', ""); 
				$this->form->setValidator('rtp_motivation', new sfValidatorString);
				$this->form->setWidget('rtp_motivation', new sfWidgetFormTextArea(array(), array('rows' => 4)));
				break;
			
		}
	}
	
	protected function dateWidget()
    {
        $widget = new sfWidgetFormInput();
        $widget->setAttribute('type', 'date');

        return $widget;
    }

	protected function processForm($id, $requestId) {
		$requesttopublish = new QubitRequestToPublish;
		
		if ($this->form->getValue('rtp_name') == null || $this->form->getValue('rtp_name') == "") {
			$rtp_name = "";
		} else {
			$rtp_name = $this->form->getValue('rtp_name');
		}
		$requesttopublish->rtp_name = $rtp_name;
		
		if ($this->form->getValue('unique_identifier') == null || $this->form->getValue('unique_identifier') == "") {
			$unique_identifier = "";
		} else {
			$unique_identifier = $this->form->getValue('unique_identifier');
		}
		$requesttopublish->parent_id = $unique_identifier; //Unique identifier
		
		if ($this->form->getValue('rtp_surname') == null || $this->form->getValue('rtp_surname') == "") {
			$rtp_surname = "";
		} else {
			$rtp_surname = $this->form->getValue('rtp_surname');
		}
		$requesttopublish->rtp_surname = $rtp_surname; //new field
		
		if ($this->form->getValue('rtp_phone') == null || $this->form->getValue('rtp_phone') == "") {
			$rtp_phone = "";
		} else {
			$rtp_phone = $this->form->getValue('rtp_phone');
		}
		$requesttopublish->rtp_phone = $rtp_phone; //new field
		
		if ($this->form->getValue('rtp_email') == null || $this->form->getValue('rtp_email') == "") {
			$rtp_email = "";
		} else {
			$rtp_email = $this->form->getValue('rtp_email');
		}
		$requesttopublish->rtp_email = $rtp_email; //
		
		if ($this->form->getValue('rtp_institution') == null || $this->form->getValue('rtp_institution') == "") {
			$rtp_institution = "";
		} else {
			$rtp_institution = $this->form->getValue('rtp_institution');
		}
		$requesttopublish->rtp_institution = $rtp_institution;

		if ($this->form->getValue('rtp_motivation') == null || $this->form->getValue('rtp_motivation') == "") {
			$rtp_motivation = "";
		} else {
			$rtp_motivation = $this->form->getValue('rtp_motivation');
		}
		$requesttopublish->rtp_motivation = $rtp_motivation;
		
		if ($this->form->getValue('rtp_planned_use') == null || $this->form->getValue('rtp_planned_use') == "") {
			$rtp_planned_use = "";
		} else {
			$rtp_planned_use = $this->form->getValue('rtp_planned_use');
		}
		$requesttopublish->rtp_planned_use = $rtp_planned_use;
		
		if ($this->form->getValue('rtp_need_image_by') == null || $this->form->getValue('rtp_need_image_by') == "") {
			$rtp_need_image_by = "";
		} else {
			$rtp_need_image_by = $this->form->getValue('rtp_need_image_by');
		}
		$requesttopublish->rtp_need_image_by = $rtp_need_image_by;
		
		if ($this->context->user->getAttribute('user_id')) {
			$requesttopublish->unique_identifier = $this->context->user->getAttribute('user_id');
		}

		
		if (isset($this->request->delete_relations)) {
			foreach ($this->request->delete_relations as $item) {
				$params = $this->context->routing->parse(Qubit::pathInfo($item));
				$params['_sf_route']->resource->delete();
			}
		}

		// send email
		$this->informationObject = QubitInformationObject::getById($id);
		$this->sendmail($this->informationObject, $rtp_name, $rtp_surname, $rtp_phone, $rtp_email, $rtp_institution, $rtp_planned_use, $rtp_motivation, $rtp_need_image_by);

		$requestToDelete = QubitCart::getById($requestId);
		$requestToDelete->delete();

	}
	
	public function sendmail($id, $rtp_name, $rtp_surname, $rtp_phone, $rtp_email, $rtp_institution, $rtp_planned_use, $rtp_motivation, $rtp_need_image_by) {

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
		$html = QubitSetting::getByNameAndScope('html', 'email');

		If ($body == "") {
			$body = "Request to Publish details:\n Name & surname: " . $rtp_name . " " . $rtp_surname . "\n Telephone: " . $rtp_phone . "\n Email address: " . $rtp_email . "\n Institution: " . $rtp_institution . "\n Planned use: " . $rtp_planned_use . "\n Motivation: " . $rtp_motivation . "\n Need image by: " . $rtp_need_image_by;
		} else {
			$body = $body . "\n Name & surname: " . $rtp_name . " " . $rtp_surname . "\n Telephone: " . $rtp_phone . "\n Email address: " . $rtp_email . "\n Institution: " . $rtp_institution . "\n Planned use: " . $rtp_planned_use . "\n Motivation: " . $rtp_motivation . "\n Need image by: " . $rtp_need_image_by;
		} 
		
		foreach ($this->pager->getResults() as $item) {
			$informationObjectsCart = QubitInformationObject::getById($item->archivalDescriptionId);
			$body = $body . "\n\n Item: " . $informationObjectsCart . " - " . $informationObjectsCart->title . " Level of Desription: " .  $informationObjectsCart->levelOfDescription . "\n";
		}
		
		// SMTP Configuration
		$mail->isSMTP();
		$mail->Host = $host; 
		$mail->SMTPAutoTLS = ($autoTLS === 'false') ? false : true;
		$mail->SMTPAuth = filter_var($smtp_auth, FILTER_VALIDATE_BOOLEAN);
		if ($smtp_auth == false)
		{
		
			$mail->Username = "";  //  
			$mail->Password = ""; // 
		} else {
			$mail->Username = $username;  
              		$mail->Password = $password; 

		}
		$mail->SMTPSecure = false; // trim(strval($smtp_secure));  //ssl Use 'tls' if using port 587
		$mail->Port = strval($port); //465

		//  sender
		$mail->From = $rtp_email; 
		$mail->FromName = $rtp_name . " " . $rtp_surname; //To address and name 
		if ($to != "") 
		{
			$mail->addAddress(trim($to), "Rock Art Research Institute (RARI)"); //Recipient name is optional
		}
		$mail->addAddress("johan@theahg.co.za", "Johan-AHG"); //Address to which recipient will reply 
		if ($reply_to != "")
		{		
			$mail->addReplyTo($reply_to, "Reply"); //CC and BCC 
		}
		if ($cc != "")
		{
			$mail->addCC($cc); 	
		}
		if ($subject != "")
		{
			$mail->Subject = $subject . " " . $id;
		}
		if ($body !="")
		{
			$mail->Body = $body;
		}

		// Content
		$htmlFlag = ($html === 'false') ? false : true;
		$mail->isHTML($hmlFlag);


		// Send email
		$mail->send();
	} catch (Exception $e) {
		echo "Email could not be sent. Error: {$mail->ErrorInfo}";
	}
	}

}
