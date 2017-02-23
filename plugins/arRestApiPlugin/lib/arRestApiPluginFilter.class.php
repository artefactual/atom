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
    catch (QubitApi404Exception $e)
    {
      $this->setErrorResponse(404, 'not-found', $e->getMessage());
    }
    catch (QubitApiNotAuthorizedException $e)
    {
      header('HTTP/1.0 401 Unauthorized');
      $this->setErrorResponse(401, 'not-authorized', $e->getMessage());
    }
    catch (QubitApiForbiddenException $e)
    {
      $this->setErrorResponse(403, 'forbidden', $e->getMessage());
    }
    catch (QubitApiBadRequestException $e)
    {
      $this->setErrorResponse(400, 'bad-request', $e->getMessage());
    }
    catch (sfStopException $e)
    {
      // Ignore stop exceptions
      return;
    }
    catch (Exception $e)
    {
      $this->setErrorResponse(500, 'internal-error', $e->getMessage());
    }
  }

  private function setErrorResponse($httpStatusCode, $errorId, $errorMessage)
  {
    $response = sfContext::getInstance()->response;

    $response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');
    $response->setStatusCode($httpStatusCode);

    // Translate exception into response data
    $responseData = array(
      'id' => $errorId,
      'message' => $errorMessage
    );

    $response->setContent($response->getContent() . arRestApiPluginUtils::arrayToJson($responseData));
  }
}
