
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

/**
 * When the "app_requires_login" setting is enabled, unauthenticated visitors are
 * redirected to the login page before they can access any part of the site.
 *
 * This setting is mutually exclusive with "read_only": if read_only is enabled
 * it takes precedence and requires_login is ignored.
 *
 * @author Daniel Lovegrove <Daniel.Lovegrove@umanitoba.ca>
 */
class QubitRequireLoginFilter extends sfFilter
{
    public const AUTH_MODULES = ['oidc', 'cas'];

    public const AUTH_ACTIONS = ['login', 'logout'];

    public function execute($filterChain)
    {
        // Only act on the initial request, not on internal forwards (error
        // pages, secure forwards, etc.).
        if (!$this->isFirstCall()) {
            $filterChain->execute();

            return;
        }

        $context = $this->getContext();
        $request = $context->getRequest();

        // Pass if:
        // - The requires_login setting is not enabled
        // - Read only mode is enabled (it takes precedence)
        // - The user is already authenticated
        if (
            !sfConfig::get('app_requires_login', false)
            || sfConfig::get('app_read_only', false)
            || $context->user->isAuthenticated()
        ) {
            $filterChain->execute();

            return;
        }

        // Pass if this is an authentication route, otherwise users could never
        // reach the login page.
        if ($this->isAuthRoute(
            $request->getParameter('module'),
            $request->getParameter('action')
        )) {
            $filterChain->execute();

            return;
        }

        // Redirect to the login page, preserving the originally requested URI so
        // the user can be returned to it after a successful login.
        $loginUrl = $context->getController()->genUrl([
            'module' => sfConfig::get('sf_login_module'),
            'action' => sfConfig::get('sf_login_action'),
            'next' => $request->getUri(),
        ]);

        $context->getController()->redirect($loginUrl);

        throw new sfStopException();
    }

    public function isAuthRoute($module, $action)
    {
        if (in_array($module, self::AUTH_MODULES, true)) {
            return true;
        }

        return sfConfig::get('sf_login_module') == $module && in_array($action, self::AUTH_ACTIONS, true);
    }
}
