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

/**
 * Controller for editing func information.
 *
 * @package    AccesstoMemory
 * @subpackage function
 * @author     David Juhasz <david@artefactual.com>
 */
class FunctionEditAction extends DefaultEditAction
{
  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = new QubitFunction;
    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      // Check user authorization
      if (!QubitAcl::check($this->resource, 'update') && !QubitAcl::check($this->resource, 'translate'))
      {
        QubitAcl::forwardUnauthorized();
      }

      // Add optimistic lock
      $this->form->setDefault('serialNumber', $this->resource->serialNumber);
      $this->form->setValidator('serialNumber', new sfValidatorInteger);
      $this->form->setWidget('serialNumber', new sfWidgetFormInputHidden);
    }
    else
    {
      // Check authorization
      if (!QubitAcl::check($this->parent, 'create'))
      {
        QubitAcl::forwardUnauthorized();
      }
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'function'));
      }
    }

    QubitDescription::addAssets($this->response);
  }
}
