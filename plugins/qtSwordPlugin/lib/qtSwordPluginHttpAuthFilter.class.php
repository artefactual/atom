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

class qtSwordPluginHttpAuthFilter extends sfFilter
{
  public function execute($filterChain)
  {
    if ($this->isFirstCall())
    {
      if (!isset($_SERVER['PHP_AUTH_USER']))
      {
        $this->sendHeaders();

        exit;
      }

      $authenticated = sfContext::getInstance()->user->authenticate($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);

      if (!$authenticated)
      {
        $this->sendHeaders();

        return;
      }
    }

    $filterChain->execute();
  }

  private function sendHeaders()
  {
    header('WWW-Authenticate: Basic realm="Secure area"');
    header('HTTP/1.0 401 Unauthorized');
  }
}
