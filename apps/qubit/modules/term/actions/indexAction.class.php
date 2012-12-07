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

class TermIndexAction extends sfAction
{
  public function checkForRepeatedNames($validator, $value)
  {
    $criteria = new Criteria;
    $criteria->add(QubitTerm::ID, $this->resource->id, Criteria::NOT_EQUAL);
    $criteria->add(QubitTerm::TAXONOMY_ID, $this->resource->taxonomyId);
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::CULTURE, $this->context->user->getCulture());
    $criteria->add(QubitTermI18n::NAME, $value);

    if (0 < intval(BasePeer::doCount($criteria)->fetchColumn(0)))
    {
      throw new sfValidatorError($validator, $this->context->i18n->__('Name - A term with this name already exists.'));
    }
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;
    if (!$this->resource instanceof QubitTerm)
    {
      $this->forward404();
    }

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    if (QubitAcl::check($this->resource, 'update'))
    {
      $validatorSchema = new sfValidatorSchema;
      $values = array();

      $validatorSchema->name = new sfValidatorCallback(array('callback' => array($this, 'checkForRepeatedNames')));
      $values['name'] = $this->resource->getName(array('cultureFallback' => true));

      try
      {
        $validatorSchema->clean($values);
      }
      catch (sfValidatorErrorSchema $e)
      {
        $this->errorSchema = $e;
      }
    }
  }
}
