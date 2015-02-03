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

class QubitSessionStorage extends sfSessionStorage
{
  public function initialize($options = null)
  {
    // http://trac.symfony-project.org/ticket/5683
    if (!isset($options['session_cookie_path']))
    {
      $options['session_cookie_path'] = sfContext::getInstance()->request->getRelativeUrlRoot();
      if (1 > strlen($options['session_cookie_path']))
      {
        $options['session_cookie_path'] = '/';
      }
    }

    // Ignore session_cookie_secure if we are not using HTTPS
    if (isset($options['session_cookie_secure']) && true === $options['session_cookie_secure'])
    {
      $request = sfContext::getInstance()->getRequest();
      if (!$request->isSecure())
      {
        unset($options['session_cookie_secure']);
      }
    }

    parent::initialize($options);
  }

  public function regenerate($destroy = false)
  {
    // session_regenerate_id(true) deletes the old session file and submits a new session cookie
    // with the new session id, but it doesn't overwrite the old session values and the 'Set-Cookie'
    // header is sent twice (possible fix in PHP 5.6 -> https://github.com/php/php-src/pull/795)
    // In GET requests that perform login, the previous session needs to be destroyed before
    // calling session_regenerate_id(true) to fix this problem
    if (!self::$sessionIdRegenerated && $destroy && self::$sessionStarted
      && sfContext::getInstance()->request->getMethod() == 'GET')
    {
      session_destroy();
    }

    parent::regenerate($destroy);
  }
}
