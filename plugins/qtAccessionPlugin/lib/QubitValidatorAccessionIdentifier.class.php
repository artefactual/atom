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

class QubitValidatorAccessionIdentifier extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('resource');
  }

  protected function doClean($value)
  {
    // Before allowing use of proposed identifier, we'll check if it has been used
    $criteria = new Criteria;
    $criteria->add(QubitAccession::IDENTIFIER, $value);

    // If accession isn't new, make sure no accession other than it is using proposed identifier
    if (isset($this->getOption('resource')->id))
    {
      // If accession isn't new, make sure no accession other than it is using proposed identifier
      $criteria->add(QubitAccession::ID, $this->getOption('resource')->id, Criteria::NOT_EQUAL);      
    }

    if (0 == count(QubitAccession::get($criteria)))
    {
      return $value;
    }

    throw new sfValidatorError($this, sfContext::getInstance()->i18n->__('This identifer is already in use.'), array('value' => $value));
  }
}
