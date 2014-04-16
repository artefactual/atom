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
class RightPostAction extends sfAction
{
  protected $validationErrors = [];

  public function execute($request)
  {
    $this->request = &$request;

  	$this->setLayout(false);
  	$this->setTemplate(null);

  	print_r($request->getParameterHolder());

  	if( $id = $request->getParameter('id') ) {
  		$right = QubitRights::getById($id);
  	} else {
  		$right = new QubitRights;
  	}

  	// validate and apply basis
  	if(! $this->validateParams() )
    {
      var_dump($this->validationErrors);
      return $this->renderText("\n\nfalse\n");
    }
    $right->basisId = $request->getParameter('basis');

    $right->startDate = $request->getParameter('startDate');
    $right->endDate = $request->getParameter('endDate');
    $right->rightsNote = $request->getParameter('rightsNote');

    // handle specific basis types
    switch ($right->basisId) {
      case QubitTerm::RIGHT_BASIS_COPYRIGHT_ID:
        $this->processCopyrightProperties();
        break;
      case QubitTerm::RIGHT_BASIS_LICENSE_ID:
        $this->processLicenseProperties();
        break;
      case QubitTerm::RIGHT_BASIS_STATUTE_ID:
        $this->processStatuteProperties();
        break;
    }

    $result = $right->save();

  	if( $request->getParameter('copyrightStatusId') )
  	{
      $copyrightStatus = QubitTerm::getById($request->getParameter('copyrightStatusId'));
  	}
  
	  return $this->renderText("\n\ntrue\n");
  }

  protected function processLicenseProperties()
  {

  }

  protected function processCopyrightProperties()
  {

  }

  protected function processStatuteProperties()
  {

  }

  protected function validateParams()
  {
    return (
      $this->validateBasis('basis')
      &&
      $this->validateDateString('startDate')
      &&
      $this->validateDateString('endDate')
    );
  }

  protected function validateDateString($value)
  {
    // expecting yyyyy-mm-dd
    $date = explode('-', $this->request->getParameter($value));
    if(! checkdate($date[1], $date[2], $date[0]))
    {
      $this->validationErrors[] = array('Invalid Date', $date, $value);
      return false;
    }

    return true;
  }
    
  protected function validateBasis($value)
  {
    $new_basis = QubitTerm::getById($this->request->getParameter($value));
    $valid_basis = [];

    foreach(QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $basis)
    {
      $valid_basis[] = $basis;
    }

    if(! in_array($new_basis, $valid_basis) )
    {
      $this->validationErrors[] = array('Invalid Basis', $basis_id, $value);
      return false;
    }

    return true;
  }
}