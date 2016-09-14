<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
  *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class InformationObjectBoxLabelAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    $this->type = $this->context->i18n->__('Box label');

    if (!isset($this->resource))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      QubitAcl::forwardUnauthorized();
    }

    $this->form = new sfForm;

    $choices = array('html' => 'HTML', 'csv' => 'CSV');
    $name = 'format';

    $this->form->setDefault($name, 'html');
    $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
    $this->form->setWidget($name, new sfWidgetFormChoice(array('expanded' => true, 'choices' => $choices)));

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      {
        $this->initiateReportGeneration();
        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }

    return 'Criteria';
  }

  private function initiateReportGeneration()
  {
    sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));

    $params = array(
        'objectId' => $this->resource->id,
        'reportType' => 'boxLabel',
        'reportTypeLabel' => $this->context->i18n->__('Box label'),
        'reportFormat' => $this->form->format->getValue(),
    );

    QubitJob::runJob('arGenerateReportJob', $params);

    $reportsUrl = url_for(array($this->resource, 'module' => 'informationobject', 'action' => 'reports'));
    $message = $this->context->i18n->__('Report generation has started, please check the <a href="%1">reports</a> page again soon.',
                                        array('%1' => $reportsUrl));

    $this->getUser()->setFlash('notice', $message);
  }
}
