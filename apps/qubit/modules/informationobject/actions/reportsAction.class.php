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

class InformationObjectReportsAction extends sfAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'report'
    );

  protected function addField($name)
  {
    switch ($name)
    {
      case 'report':

        // Hide if DC or MODS since they don't use such levels of description
        if (!in_array($this->resource->sourceStandard, array('Dublin Core Simple version 1.1', 'MODS version 3.3')))
        {
          $choices = array();

          if ($this->resource->containsLevelOfDescription('File')) {
            $choices[$this->context->routing->generate(null, array($this->resource, 'module' => 'informationobject', 'action' => 'fileList'))] = $this->context->i18n->__('File list');
          }

          if ($this->resource->containsLevelOfDescription('Item')) {
            $choices[$this->context->routing->generate(null, array($this->resource, 'module' => 'informationobject', 'action' => 'itemList'))] = $this->context->i18n->__('Item list');
          }
        }
        else
        {
          $choices = array();
        }

        if ($this->getUser()->isAuthenticated())
        {
          $choices[$this->context->routing->generate(null, array($this->resource, 'module' => 'informationobject', 'action' => 'storageLocations'))] = $this->context->i18n->__('Physical storage locations');
          $choices[$this->context->routing->generate(null, array($this->resource, 'module' => 'informationobject', 'action' => 'boxLabelCsv'))] = $this->context->i18n->__('Box label CSV');
        }


        $this->reportsAvailable = !empty($choices);

        if ($this->reportsAvailable) {
          $available_routes = array_keys($choices);
          $this->form->setDefault($name, $available_routes[0]);
          $this->form->setValidator($name, new sfValidatorChoice(array('choices' => array_keys($choices))));
          $this->form->setWidget($name, new sfWidgetFormChoice(array(
            'expanded' => true,
            'choices' => $choices)));
        }

        break;
    }
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (!isset($this->resource))
    {
      $this->forward404();
    }

    $this->form = new sfForm;

    foreach ($this::$NAMES as $name)
    {
      $this->addField($name);
    }

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->redirect($this->form->getValue('report'));
      }
    }
  }
}
