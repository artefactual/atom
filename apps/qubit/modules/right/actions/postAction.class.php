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

  public function execute($request)
  {
  	$this->setLayout(false);
  	$this->setTemplate(null);

  	print_r($request->getParameterHolder());
  	die();

  	if( $id = $request->getParameter('id') ) {
  		$right = QubitRights::getById($id);
  	} else {
  		$right = new QubitRights;
  	}

  	// validate/load existing associated objects
  	$basis = QubitTerm::getById($request->getParameter('basis'));
  	
	    
 	// $right = new QubitRights;
	// $right->act = $sourceRight->act;
	// $right->startDate = $sourceRight->startDate;
	// $right->endDate = $sourceRight->endDate;
	// $right->basis = $sourceRight->basis;

    return $this->renderText("SUCCESS\n");
  }
}