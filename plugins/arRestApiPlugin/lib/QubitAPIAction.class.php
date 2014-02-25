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

class QubitAPIAction extends sfAction
{
  public function execute($request)
  {
    $this->response->setHttpHeader('Content-Type', 'application/json; charset=utf-8');

    $method = strtoupper($request->getMethod());
    if (!method_exists($this, $method))
    {
      $this->forward404();
    }

    $callable = array($this, $method);
    $params = array($request);
    $result = call_user_func_array($callable, $params);

    return $this->renderData($result);
  }

  public function renderData($data)
  {
    $options = 0;
    if ($this->context->getConfiguration()->isDebug() && defined('JSON_PRETTY_PRINT'))
    {
      $options |= JSON_PRETTY_PRINT;
    }

    return $this->renderText(json_encode($data, $options));
  }
}
