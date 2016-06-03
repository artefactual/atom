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

class JobsReportAction extends DefaultBrowseAction
{
  public function execute($request)
  {
    parent::execute($request);

    if (!$this->context->user || !$this->context->user->isAuthenticated())
    {
      QubitAcl::forwardUnauthorized();
    }

    if (!isset($request->id))
    {
      $this->forward404();
    }

    $this->job = QubitJob::getById($request->id);

    if (!$this->job)
    {
      $this->forward404();
    }
  }
}
