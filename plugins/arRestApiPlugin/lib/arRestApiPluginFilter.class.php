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

class arRestApiPluginFilter extends sfFilter
{
  public function execute($filterChain)
  {
    sfConfig::set('sf_web_debug', false);

    try
    {
      $filterChain->execute();
    }
    catch (sfStopException $e)
    {
      // Ignore stop exceptions
      return;
    }
    catch (QubitApiException $e)
    {
      $this->setErrorResponse($e);
    }
    catch (Exception $e)
    {
      $this->setErrorResponse(new QubitApiUnknownException);
    }
  }

  private function setErrorResponse(QubitApiException $e)
  {
    $response = sfContext::getInstance()->response;
    $response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    // Translate exception into response data
    $response->setStatusCode($e->getStatusCode);

    $responseData = array(
      'id' => $e->getId(),
      'message' => $e->getMessage()
    );

    $response->setContent($response->getContent() . arRestApiPluginUtils::arrayToJson($responseData));
  }
}
