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

class QubitValidatorActorDescriptionIdentifier extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->addRequiredOption('resource');
  }

  protected function doClean($value)
  {
    // Fail validation if identifier has been used by another actor
    if (self::identifierUsedByAnotherActor($value, $this->getOption('resource')))
    {
      $message = sfContext::getInstance()->i18n->__(
                   '%1%Authority record identifier%2% - value not unique.',
                   array('%1%' => '<a href="http://ica-atom.org/doc/RS-2#5.4.1">', '%2%' => '</a>'));

      throw new sfValidatorError($this, $message, array('value' => $value));
    }

    return $value;
  }

  public static function identifierUsedByAnotherActor($identifier, $byResource)
  {
    $criteria = new Criteria;
    $criteria->add(QubitActor::DESCRIPTION_IDENTIFIER, $identifier);

    // If actor isn't new, exclude it in check to see if the identifier's already been used
    if ($byResource !== null && isset($byResource->id))
    {
      $criteria->add(QubitActor::ID, $byResource->id, Criteria::NOT_EQUAL);
    }

    return count(QubitActor::get($criteria)) > 0;
  }
}
