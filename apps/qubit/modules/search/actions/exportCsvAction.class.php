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

class SearchExportCsvAction extends sfAction
{
  // export CSV represetation of descriptions occurring in search results
  public function execute($request)
  {
    if (sfContext::getInstance()->user->isAuthenticated())
    {
      $params = array(
        'params' => $request->getParameterHolder()->getAll()
      );
      QubitJob::runJob('arSearchResultExportCsvJob', $params);

      // let user know export has started
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
      $message = '<strong>Job initiated.</strong> Check <a href="'. $jobManageUrl . '">job management</a> page to download the results when it has completed.';
      $this->getUser()->setFlash('notice', $message);
    }

    $this->redirect($request->getHttpHeader('referer'));
  }
}
