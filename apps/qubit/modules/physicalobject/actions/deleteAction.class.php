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
 * Physical Object deletion
 *
 * @package    qubit
 * @subpackage physicalobject
 * @author     David Juhasz <david@artefactual.com>
 * @version    SVN: $Id
 */
class PhysicalObjectDeleteAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->resource = $this->getRoute()->resource;

    $criteria = new Criteria;
    $criteria->add(QubitRelation::SUBJECT_ID, $this->resource->id);
    $criteria->addJoin(QubitRelation::OBJECT_ID, QubitInformationObject::ID);
    $this->informationObjects = QubitInformationObject::get($criteria);

    $this->form->setValidator('next', new sfValidatorString);
    $this->form->setWidget('next', new sfWidgetFormInputHidden);

    if ($request->isMethod('delete'))
    {
      $this->form->bind($request->getPostParameters());

      $this->resource->delete();

      $next = $this->form->getValue('next');
      if (isset($next))
      {
        $this->redirect($next);
      }

      $this->redirect('@homepage');
    }
  }
}
