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

class QubitApiAction extends sfAction
{
  public function execute($request)
  {
    if (!$this->authenticateUser())
    {
      throw new QubitApiNotAuthorizedException;
    }

    return $this->process($request);
  }

  private function authenticateUser()
  {
    // Cookie-based authentication (already signed)
    if ($this->context->user->isAuthenticated())
    {
      return true;
    }

    // Basic authentication
    if (isset($_SERVER['PHP_AUTH_USER']))
    {
      if ($this->context->user->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']))
      {
        return true;
      }
    }

    // X_REST_API_KEY is and old name still checked for backward compatibility. Last attempt!
    if (null !== $key = Qubit::getHttpHeader(array('REST-API-Key', 'HTTP_X_REST_API_KEY')))
    {
      $criteria = new Criteria;
      $criteria->add(QubitProperty::NAME, 'restApiKey');
      $criteria->add(QubitPropertyI18n::VALUE, $key);
      if (null === $restApiKeyProperty = QubitProperty::getOne($criteria))
      {
        return false;
      }

      if (null === $user = QubitUser::getById($restApiKeyProperty->objectId))
      {
        return false;
      }

      $this->context->user->signIn($user);

      return true;
    }

    return false;
  }

  public function process($request)
  {
    $method = strtoupper($request->getMethod());
    if (!method_exists($this, $method))
    {
      return $this->forward404();
    }

    // Define function callable
    $fnCallable = array($this, $method);
    $fnParamaters = array($request);

    // Modern frameworks support application/json, Symfony1 is too old :)
    // AngularJS doesn't use application/x-www-form-urlencoded
    if (('PUT' == $method || 'POST' == $method) && 'application/json' == $request->getContentType())
    {
      $fnParamaters[] = json_decode($request->getContent());
    }

    // Load Qubit helper before calling it
    ProjectConfiguration::getActive()->loadHelpers(array('Asset', 'Qubit', 'Date', 'I18N'));

    // Load site base URL
    $this->siteBaseUrl = '';
    if (null !== $setting = QubitSetting::getByName('siteBaseUrl'))
    {
      $this->siteBaseUrl = $setting->getValue(array('sourceCulture' => true));
    }

    $result = call_user_func_array($fnCallable, $fnParamaters);

    return $this->renderData($result);
  }

  public function renderData($data)
  {
    if ($data === 'CSV')
    {
      return sfView::NONE;
    }

    if ($data === sfView::NONE)
    {
      $this->response->setHeaderOnly(true);

      $this->response->setStatusCode(204);

      return sfView::NONE;
    }

    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    return $this->renderText(arRestApiPluginUtils::arrayToJson($data));
  }

  protected function prepareEsPagination(\Elastica\Query &$query, $limit = null)
  {
    $limit = empty($limit) ? sfConfig::get('app_hits_per_page', 10) : $limit;
    $limit = $this->request->getGetParameter('limit', $limit);
    if ($limit > 100)
    {
      $limit = 100;
    }

    $query->setSize($limit);

    // Skip
    if (isset($this->request->skip) && ctype_digit($this->request->skip))
    {
      $query->setFrom($this->request->skip);
    }
  }

  protected function prepareEsSorting(\Elastica\Query &$query, $fields = array())
  {
    // Stop if preferred option is not set or $fields empty
    if (1 > count($fields) || !isset($this->request->sort))
    {
      return;
    }

    // Stop if the preferred option can't be found
    if (false === array_search($this->request->sort, array_keys($fields)))
    {
      return;
    }

    $sortDirection = 'asc';
    if (isset($this->request->sort_direction))
    {
      if ('desc' == $this->request->sort_direction)
      {
        $sortDirection = 'desc';
      }
    }

    // TODO: allow $request->sort to be multi-value
    $query->setSort(array($fields[$this->request->sort] => $sortDirection));
  }

  protected function addItemToArray(&$array, $key, $value)
  {
    if (empty($value))
    {
      return;
    }

    $array[$key] = $value;
  }
}
