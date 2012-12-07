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
 * @package    AccesstoMemory
 * @subpackage search
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class QubitArrayPager extends sfPager
{
  protected
    $hits;

  public function __construct()
  {
    parent::__construct(null);
  }

  public function __get($name)
  {
    if ('hits' == $name) 
    {
      return $this->hits;
    }
    else
    {
      return call_user_func(array($this, 'get'.ucfirst($name)));
    }
  }

  public function __set($name, $val)
  {
    if ('hits' == $name) 
    {
      $this->hits = $val;

      $this->nbResults = count($this->hits);
      $this->lastPage = ceil($this->nbResults / $this->getMaxPerPage());
    }
    else
    {
      call_user_func(array($this, 'set'.ucfirst($name)));
    }
  }

  public function getResults()
  {
    return array_slice($this->hits, $this->getFirstIndice() - 1, $this->getMaxPerPage());
  }
}
