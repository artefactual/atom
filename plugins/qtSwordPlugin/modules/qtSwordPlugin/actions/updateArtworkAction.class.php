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

class qtSwordPluginUpdateArtworkAction extends sfAction
{
  public function execute($request)
  {
    $this->response->setHeaderOnly(true);

    if ($request->isMethod('post') && ctype_digit($request->tmsId))
    {
      try
      {
        // Put the job in the background if the queue support is enabled
        if (sfConfig::get('app_use_job_scheduler', true))
        {
          $client = new Net_Gearman_Client('localhost:4730');
          $handle = $client->arUpdateArtworkWorker($request->tmsId);

          // Job accepted!
          $this->response->setStatusCode(202);
        }
        // Otherwise, run it sinchronously (not a good idea)
        else
        {
          // TODO?
        }
      }
      catch (Exception $e)
      {
        $this->response->setStatusCode(500);
      }
    }
    else
    {
      $this->response->setStatusCode(400, $this->context->i18n->__('Bad request'));
    }

    return sfView::NONE;
  }
}
