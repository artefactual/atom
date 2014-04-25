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

    $this->object = self::loadInformationObject($request->getParameter('object_slug'));

  	$this->setLayout(false);

  	// print_r($request->getParameterHolder());

  	if( $id = $request->getParameter('id') ) {
  		$right = QubitRights::getById($id);
  	} else {
  		$right = new QubitRights;

      // set the related object

  	}

  	// validate and apply basis
  	if(! $this->validateParams() )
    {
      var_dump($this->validationErrors);
      $this->setTemplate(null);
      return $this->renderText(json_encode($this->validationErrors));
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

    // if this is a new right instance
    // then we need to record a relation
    // record to associate to its parent
    // object
    if( ! $request->getParameter('id') )
    {
      $relation = new QubitRelation;
      $relation->objectId = $right->id;
      $relation->typeId = QubitTerm::RIGHT_ID;
      $relation->subjectId = $this->object->id;
      $relation->save();
    }

  	if( $request->getParameter('copyrightStatusId') )
  	{
      $copyrightStatus = QubitTerm::getById($request->getParameter('copyrightStatusId'));
  	}
  
    // find the newly modifid resource and 
    // render it with _right template
    // ajax will update the page with it
	  $this->resource = QubitRights::getById($right->id);
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
      $this->validateDateString('startDate', true)
      &&
      $this->validateDateString('endDate', true)
    );
  }

  protected function validateDateString($value, $allowNull=false)
  {
    $value = $this->request->getParameter($value);
    // expecting yyyyy-mm-dd
    if($allowNull && empty($value)) {
      return true;
    }

    $date = explode('-', $value);
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

  static function loadInformationObject($slug)
  {
    $object = QubitObject::getBySlug($slug);
    if (!isset($object))
    {
      throw new sfError404Exception;
    }
    return $object;
  }
}