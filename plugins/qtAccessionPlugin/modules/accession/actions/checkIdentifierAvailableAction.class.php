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

class AccessionCheckIdentifierAvailableAction extends sfAction
{
  public function execute($request)
  {
    // Check user authorization
    if (!QubitAcl::check($this->resource, 'create') && !QubitAcl::check($this->resource, 'update'))
    {
      $this->getResponse()->setStatusCode(401);
      return sfView::NONE;
    }

    $this->getResponse()->setContentType('application/json');

    // Assemble response data
    $valid = $this->validateAccessionIdentifier($request->identifier, $request->accession_id);
    $message = ($valid) ? $this->context->i18n->__('Identifier available.') : $this->context->i18n->__('Identifier unavailable.');
    $responseData = array('allowable' => $valid, 'message' => $message);

    $this->getResponse()->setContent(json_encode($responseData));

    return sfView::NONE;
  }

  private function validateAccessionIdentifier($identifier, $accessionId = null)
  {
    if (!empty($accessionId))
    {
      // Attempt to load existing accession
      $resource = QubitAccession::getById($accessionId);

      // Indicate bad request if accession doesn't exist
      if (null === $resource)
      {
        $this->getResponse()->setStatusCode(400);
        return false;
      }
    }
    else
    {
      // Create new accession so validator can be run
      $resource = new QubitAccession;
    }

    $validator = new QubitValidatorAccessionIdentifier(array('required' => true, 'resource' => $resource));

    try
    {
      $validator->clean($identifier);
      return true;
    }
    catch (sfValidatorError $e)
    {
      return false;
    }
  }
}
