<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Display warning when repository upload limit is exceeded
 *
 * @package    qubit
 * @subpackage repository
 * @author     david juhasz <david@artefactual.com>
 * @version    SVN: $Id: uploadLimitExceededAction.class.php 10288 2011-11-08 21:25:05Z mj $
 */
class RepositoryUploadLimitExceededAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = null;

    if (isset($this->getRoute()->resource))
    {
      $this->resource = $this->getRoute()->resource;
    }
  }
}
