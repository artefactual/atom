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

class InformationObjectExportCsvAction extends sfAction
{
  // Export CSV representation of descriptions occurring in search/browse results
  public function execute($request)
  {
    if ($this->context->user->isAuthenticated())
    {
      if ($request->fromClipboard)
      {
        $options = array('params' => array('fromClipboard' => true, 'slugs' => $this->context->user->getClipboard()->getAll()));
      }
      else
      {
        // To keep the top level descriptions filter an agg in sync
        // the autocomplete value is converted to the resource id
        // before the agg filters are added to the query
        $getParameters = $request->getGetParameters();
        if (isset($getParameters['collection']) && !ctype_digit($getParameters['collection']))
        {
          $params = sfContext::getInstance()->routing->parse(Qubit::pathInfo($getParameters['collection']));
          $collection = $params['_sf_route']->resource;

          unset($getParameters['collection']);

          if ($collection instanceof QubitInformationObject)
          {
            $getParameters['collection'] = $collection->id;
          }
        }

        // Add first criterion to the search box if it's over any field
        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->sq0) && !isset($request->sf0))
        {
          $getParameters['query'] = $request->sq0;
        }

        // And search box query as the first criterion
        if (1 !== preg_match('/^[\s\t\r\n]*$/', $request->query))
        {
          $getParameters['sq0'] = $request->query;
        }

        $options = array('params' => $getParameters);
      }

      QubitJob::runJob('arInformationObjectCsvExportJob', $options);

      // Let user know export has started
      sfContext::getInstance()->getConfiguration()->loadHelpers(array('Url'));
      $jobManageUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
      $message = '<strong>Export of descriptions initiated.</strong> Check <a href="'. $jobManageUrl . '">job management</a> page to download the results when it has completed.';
      $this->getUser()->setFlash('notice', $message);
    }

    // If referer URL is valid, redirect to it... otherwise, redirect to the information objects browse page)
    if (filter_var($request->getHttpHeader('referer'), FILTER_VALIDATE_URL) === true)
    {
      $this->redirect($request->getHttpHeader('referer'));
    }
    else
    {
      $this->redirect($this->context->routing->generate(null, array(null, 'module' => 'informationobject', 'action' => 'browse')));
    }
  }
}
