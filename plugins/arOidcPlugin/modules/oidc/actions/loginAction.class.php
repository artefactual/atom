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

class OidcLoginAction extends sfAction
{
    public function execute($request)
    {
        // Redirect to @homepage if the user is already authenticated
        // or the read only mode is enabled
        if (sfConfig::get('app_read_only', false) || $this->context->user->isAuthenticated()) {
            $this->redirect('@homepage');
        }

        // Save referring page URL. The request will be oidc/login but the referrer will be @homepage, or
        // user/list (for example) if the user was attempting to access a secure resource. When redirected
        // back from the OIDC endpoint, the referrer will be empty.
        if ($request->isMethod('post') && !empty($request->getReferer())) {
            $this->context->user->setAttribute('atom-login-referer', $request->getReferer());
        }

        if ($request->isMethod('post') || isset($_REQUEST['code'])) {
            if (null !== $providerId = $this->context->user->parseProviderIdFromUrl($this->context->user->getAttribute('atom-login-referer', null))) {
                $this->context->user->validateProviderId($providerId, true);
            }

            $result = $this->context->user->authenticate();
            if (false == $result) {
                $this->context->user->logout(false);

                $this->redirect('admin/secure');
            }
        }

        // Redirect to module/action the user was trying to reach before being redirected
        // to the OIDC IAM system for authentication. We prefer a redirect to a forward so that the ticket
        // parameter is not accidentally exposed in the user's browser.
        if (null !== $redirectUrl = $this->context->user->getAttribute('atom-login-referrer', null)) {
            $this->context->user->setAttribute('atom-login-referer', null);
            $this->redirect($redirectUrl);
        }

        $this->redirect('@homepage');
    }
}
