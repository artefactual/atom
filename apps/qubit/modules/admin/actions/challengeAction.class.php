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

class AdminChallengeAction extends sfAction
{
    public function execute($request)
    {
        if (false === sfConfig::get('app_user_challenge_activated', false)) {
            return $this->redirect('@homepage');
        }
        // Read configuration values from app.yml
        $this->cookieNameHasJs = sfConfig::get('app_user_challenge_cookiename_js', 'atom_js');
        $this->cookieNameHeadless = sfConfig::get('app_user_challenge_cookiename_headless', 'atom_headless');
        $salt = sfConfig::get('app_user_challenge_salt');
        $this->delaySeconds = sfConfig::get('app_user_challenge_delay_seconds', 5);

        $requestedUri = $this->context->user->getAttribute('atom-challenge-requested-uri', null);

        if (null !== $requestedUri) {
            $this->requestedUri = $this->parseUrl($requestedUri);

            $this->context->user->getAttributeHolder()->remove('atom-challenge-requested-uri');
        } else {
            $this->requestedUri = $this->context->controller->genUrl('@homepage', true);
        }

        // Get the client's IP address (as seen by the server).
        $clientIp = $request->getRemoteAddress();

        // Calculate the expected cookie value.
        $this->cookieValue = md5($clientIp.':'.$salt);
    }

    /**
     * Takes a full url, removes the base url part and returns the result.
     *
     * @param mixed $url
     */
    protected function parseUrl($url)
    {
        $parts = parse_url($url);
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? '?'.$parts['query'] : '';
        $fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';

        return $path.$query.$fragment;
    }
}
