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

class QubitRoute extends sfRoute
{
  protected function filterParams($params)
  {
    // Fill in missing parameters with attributes of $params[0]
    if (!is_array($params))
    {
      $params = array($params);
    }

    if (isset($params[0]))
    {
      if ($params[0] instanceof sfOutputEscaper)
      {
        $params[0] = sfOutputEscaper::unescape($params[0]);
      }

      foreach (array_diff_key($this->params + $this->variables, $params) as $key => $ignore)
      {
        try
        {
          $params[$key] = $params[0][$key];
        }
        catch (sfException $e)
        {
        }
      }

      unset($params[0]);
    }

    return $params;
  }

  /**
   * @see sfRoute
   */
  public function matchesParameters($params, $context = array())
  {
    return parent::matchesParameters($this->filterParams($params), $context);
  }

  /**
   * @see sfRoute
   */
  public function generate($params, $context = array(), $absolute = false)
  {
    return parent::generate($this->filterParams($params), $context, $absolute);
  }
}
