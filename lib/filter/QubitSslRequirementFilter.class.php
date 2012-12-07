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

class QubitSslRequirementFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $context = $this->getContext();
    $request = $context->getRequest();

    # Pass if:
    # - Debug mode is enabled
    # - Client is already using HTTPS
    # - Setting require_ssl_admin is not enabled
    if ($context->getConfiguration()->isDebug() ||
        $request->isSecure() ||
        !sfConfig::get('app_require_ssl_admin'))
    {
      $filterChain->execute();

      return;
    }

    if ($context->user->isAuthenticated() ||
        ('user' == $request->getParameter('module') &&
          'login' == $request->getParameter('action')))
    {
      $secure_url = str_replace('http', 'https', $request->getUri());

      return $context->getController()->redirect($secure_url);
    }
    else
    {
      $filterChain->execute();
    }
  }
}
