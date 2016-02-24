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

class arRestApiPluginTokenAuthFilter extends sfFilter
{
  public function execute($filterChain)
  {
    if ($this->isFirstCall())
    {
      if (!isset($_SERVER['HTTP_X_REST_API_KEY']))
      {
        $this->sendHeaders();

        return;
      }

      $criteria = new Criteria;
      $criteria->add(QubitProperty::NAME, 'restApiKey');
      $criteria->add(QubitPropertyI18n::VALUE, $_SERVER['HTTP_X_REST_API_KEY']);

      if (null == $restApiKeyProperty = QubitProperty::getOne($criteria))
      {
        $this->sendHeaders();

        return;
      }

      // Authenticate user so ACL checks can be applies in XML template# get user ID from property?
      $user = QubitUser::getById($restApiKeyProperty->objectId);

      if (null === $user)
      {
        $this->sendHeaders();

        return;
      }

      $this->context->user->signIn($user);
    }

    $filterChain->execute();
  }

  private function sendHeaders()
  {
    header('HTTP/1.0 401 Unauthorized');
  }
}
