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

class RightManageAction extends sfAction
{
  protected function earlyExecute()
  {
    $this->resource = $this->getRoute()->resource;

    // if we haven't got a resource, we have a problem houston
    if (null === $this->resource)
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }
  }

  protected function formSetup()
  {
    $this->form = new sfForm;
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', false);

    $this->form->setWidget('all_or_digital_only', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices'  => array('all' => $this->context->i18n->__('Apply to all descendants'),
                          'digital_only' => $this->context->i18n->__('Apply only to %1% descendants', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject'))))),
      'default'  => 'all'
    )));

    $choices = array(
      'overwrite' => $this->context->i18n->__('Delete current rights in descendants and replace with parent rights.'),
      'combine' => $this->context->i18n->__('Keep current rights in descendants and add parent rights.')
    );

    $this->form->setWidget('overwrite_or_combine', new sfWidgetFormChoice(array(
      'expanded' => true,
      'choices'  => $choices,
      'default'  => 'overwrite'
    )));

    $this->form->setValidators(array(
      'all_or_digital_only' => new sfValidatorChoice(array('choices' => array('all', 'digital_only'), 'required' => true)),
      'overwrite_or_combine' => new sfValidatorChoice(array('choices' => array('overwrite', 'combine'), 'required' => true))
    ));
  }

  public function execute($request)
  {
    $this->earlyExecute();
    $this->formSetup();

    if ($request->isMethod('post'))
    {
      $params = $request->getPostParameters();
      $this->form->bind($params);

      if ($this->form->isValid())
      {
        // Set job params
        $jobParams = $this->form->getValues();
        $jobParams['objectId'] = $this->resource->getId();

        $jobParams['name'] = $this->context->i18n->__('Inherit rights');

        $desc = $this->context->i18n->__('Children inheriting rights from record: ') .
                $this->resource->getTitle(array('cultureFallback' => true));

        $jobParams['description'] = $desc;

        // Queue job with params
        $job = QubitJob::runJob('arInheritRightsJob', $jobParams);

        // redirect to info object view page
        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }
}
