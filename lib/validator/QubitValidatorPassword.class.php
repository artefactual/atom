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

class QubitValidatorPassword extends sfValidatorString
{
  protected function configure($options = array(), $messages = array())
  {
    parent::configure($options, $messages);

    $this->setOption('min_length', 8);
  }

  protected function doClean($value)
  {
    $value = parent::doClean($value);

    $score = 0;

    // Check 1: contains upper case letters
    if (preg_match('/[A-Z]/', $value))
    {
      $score++;
    }

    // Check 2: contains lower case letters
    if (preg_match('/[a-z]/', $value))
    {
      $score++;
    }

    // Check 3: contains numbers
    if (preg_match('/[0-9]/', $value))
    {
      $score++;
    }

    // Check 4: contains everything but 1), 2) and 3) (special characters)
    if (preg_match('/[^A-Za-z0-9]/', $value))
    {
      $score++;
    }

    // If less than three checks were passed
    if ($score < 3)
    {
      throw new sfValidatorError($this, 'invalid', array('value' => null));
    }

    return $value;
  }
}
