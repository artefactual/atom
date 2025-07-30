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
 * Archival Description RequestToPublish edit component.
 *
 * @package    qubit
 * @subpackage Archival Description RequestToPublish
 * @author     Johan Pieterse <johan@plainsailingisystems.co.za> 
 * @version    SVN: $Id
 */
class RequestToPublishEditRequestToPublishAction extends DefaultEditAction {
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
		'completed_at', 
		'status_id', 
		'outcome'
	);

	protected function earlyExecute() {
		$this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
		$this->resource = $this->getRoute()->resource;	
	}
	
	protected function addField($name) {
		switch ($name) {
			case 'unique_identifier':
				$this->form->setDefault('unique_identifier', $this->resource->unique_identifier); 
				$this->form->setValidator('unique_identifier', new sfValidatorString);
				$this->form->setWidget('unique_identifier', new sfWidgetFormInput);
				break;
			
			case 'rtp_name':
				$this->form->setDefault('rtp_name', $this->resource->rtp_name); 
				$this->form->setValidator('rtp_name', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_name', new sfWidgetFormInput);
				break;
			
			case 'rtp_surname':
				$this->form->setDefault('rtp_surname', $this->resource->rtp_surname); 
				$this->form->setValidator('rtp_surname', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_surname', new sfWidgetFormInput);
				break;
			
			case 'rtp_phone':
				$this->form->setDefault('rtp_phone', $this->resource->rtp_phone); 
				$this->form->setValidator('rtp_phone', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_phone', new sfWidgetFormInput);
				break;
			
			case 'rtp_email':
				$this->form->setDefault('rtp_email', $this->resource->rtp_email); 
				$this->form->setValidator('rtp_email', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_email', new sfWidgetFormInput);
				break;
			
			case 'rtp_planned_use':
				$this->form->setDefault('rtp_planned_use', $this->resource->rtp_planned_use); 
				$this->form->setValidator('rtp_planned_use', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_planned_use', new sfWidgetFormTextArea(array(), array('rows' => 2)));
				break;
			
			case 'rtp_institution':
				$this->form->setDefault('rtp_institution', $this->resource->rtp_institution); 
				$this->form->setValidator('rtp_institution', new sfValidatorString(['required' => true]));
				$this->form->setWidget('rtp_institution', new sfWidgetFormTextArea(array(), array('rows' => 2)));
				break;
			
			case 'rtp_need_image_by':
				$this->form->setDefault('rtp_need_image_by', $this->resource->rtp_need_image_by);
				$this->form->setValidator('rtp_need_image_by', new sfValidatorString);
				$this->form->setWidget('rtp_need_image_by', new sfWidgetFormInput);
				break;
			
			case 'rtp_motivation':
				$this->form->setDefault('rtp_motivation', $this->resource->rtp_motivation); 
				$this->form->setValidator('rtp_motivation', new sfValidatorString);
				$this->form->setWidget('rtp_motivation', new sfWidgetFormTextArea(array(), array('rows' => 4)));
				break;
			
			case 'status_id':
				$this->form->setDefault('statusId', $this->resource->statusId); 
				$this->form->setValidator('statusId', new sfValidatorString);
				$this->form->setWidget('statusId', new sfWidgetFormInput);
				break;
			
			case 'created_at':
				$this->form->setDefault('createdAt', $this->resource->createdAt); 
				$this->form->setValidator('createdAt', new sfValidatorString);
				$this->form->setWidget('createdAt', new sfWidgetFormInput);
				break;
			
			case 'completed_at':
				$this->form->setDefault('completedAt', $this->resource->completedAt); 
				$this->form->setValidator('completedAt', new sfValidatorString);
				$this->form->setWidget('completedAt', new sfWidgetFormInput);
				break;
			
			case 'outcome':
				$choices = array('In review', 'Rejected', 'Approved');
				$this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
				$this->form->setWidget($name, new arWidgetFormSelectRadio(array('choices' => $choices, 'class' => 'radio inline')));
				break;

		        break;
				
			default:
				return parent::addField($name);
		}
	}
	
	protected function processForm() {
		if (null !== $this->form->getValue('rtp_name') || 
		null !== $this->form->getValue('rtp_surname') || 
		null !== $this->form->getValue('rtp_phone') || 
		null !== $this->form->getValue('rtp_email') || 
		null !== $this->form->getValue('rtp_institurion') || 
		null !== $this->form->getValue('rtp_planned_use')) {
			$requesttopublish = $this->resource;
			
			if ($this->form->getValue('unique_identifier') == null || $this->form->getValue('unique_identifier') == "") {
				$unique_identifier = "";
			} else {
				$unique_identifier = $this->form->getValue('unique_identifier');
			}
			$requesttopublish->parent_id = $unique_identifier; //Unique identifier
			
			if ($this->form->getValue('rtp_name') == null || $this->form->getValue('rtp_name') == "") {
				$rtp_name = "";
			} else {
				$rtp_name = $this->form->getValue('rtp_name');
			}
			$requesttopublish->rtp_name = $rtp_name;
			
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
			
			if ($this->form->getValue('rtp_institurion') == null || $this->form->getValue('rtp_institurion') == "") {
				$rtp_institurion = "";
			} else {
				$rtp_institurion = $this->form->getValue('rtp_institurion');
			}
			$requesttopublish->rtp_institurion = $rtp_institurion;

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

			if ($this->form->getValue('outcome') == null || $this->form->getValue('outcome') == "") {
				$outcome = "";
			} else {
				$outcome = $this->form->getValue('outcome');
			}
			if ($outcome == 2) {
				$outcome = QubitTerm::APPROVED_ID;
			} else if ($outcome == 1) {
				$outcome = QubitTerm::REJECTED_ID;
			} else if ($outcome == 0) {
				$outcome = QubitTerm::IN_REVIEW_ID;
			} else {
				$outcome = QubitTerm::IN_REVIEW_ID;
			}

			$requesttopublish->rtp_planned_use = $rtp_planned_use;
			$requesttopublish->completedAt = date('Y-m-d H:i:s');
			$requesttopublish->statusId = $outcome; //QubitTerm::IN_REVIEW_ID;
			$informationObj = QubitInformationObject::getById($this->resource->id);
			$requesttopublish->save();
			$this->requesttopublish =  $requesttopublish->id;
		}
		
		if (isset($this->request->delete_relations)) {
			foreach ($this->request->delete_relations as $item) {
				$params = $this->context->routing->parse(Qubit::pathInfo($item));
				$params['_sf_route']->resource->delete();
			}
		}
	}
	
	public function execute($request) {
		parent::execute($request);
		if ($request->isMethod('post')) {
			$this->form->bind($request->getPostParameters());
			if ($this->form->isValid()) {
				$this->processForm();
				if ($this->form->getValue('outcome') != 1) {
					$this->redirect(array($this->resource, 'module' => 'requesttopublish', 'action' => 'browse'));
				}
				$this->resource->save();
				$this->redirect(array($this->resource, 'module' => 'requesttopublish', 'action' => 'browse'));
			}
		}
	}
}
