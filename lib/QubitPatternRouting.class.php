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

/**
 * QubitPatternRouting add Qubit specific functionality to the sfPatternRouting
 * class
 *
 * @package    AccesstoMemory
 * @subpackage routing
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitPatternRouting extends sfPatternRouting
{

  /**
   * @see sfPatternRouting
   */
  public function parse($url)
  {
    try
    {
      return parent::parse($url);
    }
    catch (sfError404Exception $e)
    {
      return false;
    }
  }
}
