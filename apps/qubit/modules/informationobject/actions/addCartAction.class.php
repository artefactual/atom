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
 * Physical Object edit component.
 *
 * @author     Johan Pieterse <johan.pieterse@sita.co.za>
 *
 * @version    SVN: $Id
 */
class InformationObjectAddCartAction extends DefaultEditAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;
        $this->informationObject = QubitInformationObject::getById($this->resource->id);

        // Add to Objects table
        $cart = new QubitCart();

        $cart->save();
        // Add to cart table
        $userId = $this->context->user->getAttribute('user_id');

        $sql = 'SELECT MAX(id) as mId from object where class_name = "QubitCart";';
        $statement = QubitPdo::prepareAndExecute($sql);
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $fRow = $row['mId'];
        }

        $sql = 'UPDATE cart SET user_id = "'.$userId.'",archival_description_id="'.$this->resource->id.'",archival_description="'.$this->informationObject->title.'",slug="'.$this->resource->slug.'" WHERE id = '.$fRow.';';

        QubitPdo::prepareAndExecute($sql);

        $this->redirect([$this->resource, 'module' => 'informationobject']);
    }

    protected function earlyExecute()
    {
        // $this->form->getValidatorSchema()->setOption('allow_extra_fields', true);
        $this->resource = $this->getRoute()->resource;

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->forward404();
        }
    }
}
