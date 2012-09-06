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

class ObjectImportSelectAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;

      $this->form->setDefault('parent', $this->context->routing->generate(null, array($this->resource)));
      $this->form->setValidator('parent', new sfValidatorString);
      $this->form->setWidget('parent', new sfWidgetFormInputHidden);
    }

    // Check parameter
    if (isset($request->type))
    {
      $this->type = $request->type;
    }

    switch ($this->type)
    {
      case 'csv':
        $this->title = $this->context->i18n->__('Import a CSV file');

        break;

      case 'xml':
        $this->title = $this->context->i18n->__('Import a XML file');

        break;

      default:
        $this->redirect(array('module' => 'object', 'action' => 'importSelect', 'type' => 'xml'));

        break;
    }
  }
}
