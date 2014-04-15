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


// render a modal edit rights form for a given resource
class RightModalAction extends sfAction
{

  public function execute($request)
  {
    if($id = $request->getParameter('id'))
    {
      $this->right = QubitRights::getById($id);
    } else {
      $this->right = new QubitRights();
    }

    $this->setBasisOptions();
    $this->setCopyrightStatusOptions();
    
    $this->countries = new sfWidgetFormI18nChoiceCountry(array('add_empty' => true, 'culture' => $this->context->user->getCulture()));

    $this->setLayout(false);
  }

  protected function setBasisOptions()
  {
    $this->basisOptions = [];
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item) {
      $this->basisOptions[] = array(
        'value' => $item->id,
        'text' => $item->__toString(),
        'selected' => ($item->id == $this->right->basis->id ? ' selected' : '')
      );
    }
  }

  protected function setCopyrightStatusOptions()
  {
    $this->copyrightStatusOptions = [];
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::COPYRIGHT_STATUS_ID) as $item) {
      $this->copyrightStatusOptions[] = array(
        'value' => $item->id,
        'text' => $item->__toString(),
        'selected' => ($item->id == $this->right->copyrightStatusId ? ' selected' : '')
      );
    }
  }
}
