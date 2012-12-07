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
 * Physical Object edit component.
 *
 * @package    AccesstoMemory
 * @subpackage digitalObject
 * @author     david juhasz <david@artefactual.com>
 */
class InformationObjectEditPhysicalObjectsAction extends DefaultEditAction
{
  public static
    $NAMES = array(
      'containers',
      'location',
      'name',
      'type');

  protected function earlyExecute()
  {
    $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);

    $this->resource = $this->getRoute()->resource;

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'update'))
    {
      QubitAcl::forwardUnauthorized();
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'containers':
        $this->form->setValidator('containers', new sfValidatorPass);
        $this->form->setWidget('containers', new sfWidgetFormSelect(array('choices' => array(), 'multiple' => true)));

        break;

      case 'location':
      case 'name':
        $this->form->setValidator($name, new sfValidatorString);
        $this->form->setWidget($name, new sfWidgetFormInput);

        break;

      case 'type':
        $this->form->setValidator('type', new sfValidatorString);
        $this->form->setWidget('type', new sfWidgetFormSelect(array('choices' => QubitTerm::getIndentedChildTree(QubitTerm::CONTAINER_ID, '&nbsp;', array('returnObjectInstances' => true)))));

        break;

      default:

        return parent::addField($name);
    }
  }

  protected function processForm()
  {
    foreach ($this->form->getValue('containers') as $item)
    {
      $params = $this->context->routing->parse(Qubit::pathInfo($item));
      $this->resource->addPhysicalObject($params['_sf_route']->resource);
    }

    if (null !== $this->form->getValue('name') || null !== $this->form->getValue('location'))
    {
      $physicalObject = new QubitPhysicalObject;
      $physicalObject->name = $this->form->getValue('name');
      $physicalObject->location = $this->form->getValue('location');

      $params = $this->context->routing->parse(Qubit::pathInfo($this->form->getValue('type')));
      $physicalObject->type = $params['_sf_route']->resource;

      $physicalObject->save();

      $this->resource->addPhysicalObject($physicalObject);
    }

    if (isset($this->request->delete_relations))
    {
      foreach ($this->request->delete_relations as $item)
      {
        $params = $this->context->routing->parse(Qubit::pathInfo($item));
        $params['_sf_route']->resource->delete();
      }
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    $this->relations = QubitRelation::getRelationsByObjectId($this->resource->id, array('typeId' => QubitTerm::HAS_PHYSICAL_OBJECT_ID));

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();

        $this->resource->save();

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }
}
