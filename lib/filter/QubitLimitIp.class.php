<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitLimitIpFilter extends sfFilter
{
  public function execute($filterChain)
  {
    $this->context = $this->getContext();
    $this->request = $this->context->getRequest();

    $this->limit = sfConfig::get('app_limit_admin_ip');

    # Pass if:
    # - Debug mode is on
    # - Setting "limit_admin_ip" is not set
    # - The filter is forwarding to admin/secure (isFirstCall)
    # - Route is user/logout
    if ($this->context->getConfiguration()->isDebug() ||
        !$this->limit ||
        !$this->isFirstCall() ||
        ('user' == $this->request->getParameter('module') && 'logout' == $this->request->getParameter('action')))
    {
      $filterChain->execute();

      return;
    }

    # Forward to admin/secure if not allowed (only applies if user is authenticated)
    if ($this->context->user->isAuthenticated() && !$this->isAllowed())
    {
      $this->context->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));

      throw new sfStopException();
    }

    $filterChain->execute();
  }

  protected function getRemoteAddress()
  {
    $pathInfo = $this->request->getPathInfoArray();

    return $pathInfo["REMOTE_ADDR"];
  }

  protected function isAllowed()
  {
    $limit = preg_split('/[,-]/', $this->limit);
    $address = $this->getRemoteAddress();

    // Single IP
    if (1 == count($limit) && $address == $limit[0])
    {
      return true;
    }
    // Range
    else if (2 == count($limit))
    {
      $address = ip2long($address);

      if (ip2long($limit[0]) <= $address &&
          ip2long($limit[1]) >= $address)
      {
        return true;
      }
    }

    return false;
  }
}
