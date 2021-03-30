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

class CasLoginAction extends sfAction
{
    public function execute($request)
    {
        // Redirect to @homepage if the user is already authenticated
        // or the read only mode is enabled
        if (sfConfig::get('app_read_only', false) || $this->context->user->isAuthenticated()) {
            $this->redirect('@homepage');
        }

        $this->getUser()->authenticate();

        // Redirect to module/action the user was trying to reach before being redirected
        // to CAS for authentication. We prefer a redirect to a forward so that the ticket
        // parameter is not accidentally exposed in the user's browser.
        $redirectUrl = $request->getParameter('module').'/'.$request->getParameter('action');
        $this->redirect($redirectUrl);
    }
}
