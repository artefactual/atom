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

class QubitCSP extends sfFilter
{
    public function execute($filterChain)
    {
        // Only use CSP headers if theme is b5.
        if (!sfConfig::get('app_b5_theme', false)) {
            $filterChain->execute();

            return;
        }

        // Get CSP response header from config if available.
        if (null === $cspResponseHeader = $this->getCspResponseHeader($this->getContext())) {
            // CSP is deactivated.
            $filterChain->execute();

            return;
        }

        if (null === $cspDirectives = $this->getCspDirectives($this->getContext())) {
            $filterChain->execute();

            return;
        }

        // Save nonce for use in templates.
        $nonce = $this->getRandomNonce();
        sfConfig::set('csp_nonce', 'nonce='.$nonce);

        $filterChain->execute();

        if (preg_match('~(text/xml|application/json)~', $this->getContext()->response->getContentType())) {
            return;
        }

        // Set CSP header on response.
        $this->getContext()->response->setHttpHeader(
            $cspResponseHeader,
            $cspDirectives = str_replace('nonce', 'nonce-'.$nonce, $cspDirectives)
        );
    }

    public function getCspResponseHeader($context): ?string
    {
        $cspResponseHeader = sfConfig::get('app_csp_response_header', '');

        if (empty($cspResponseHeader)) {
            // CSP is deactivated.
            return null;
        }

        if (false === array_search($cspResponseHeader, ['Content-Security-Policy-Report-Only', 'Content-Security-Policy'])) {
            $context->getLogger()->err(
                sprintf(
                    'Setting \'app_csp_response_header\' is not set properly. CSP is not being used.'
                )
            );

            return null;
        }

        return $cspResponseHeader;
    }

    public function getCspDirectives($context): ?string
    {
        $cspDirectives = trim(preg_replace('/\s+/', ' ', sfConfig::get('app_csp_directives', '')));

        if (empty($cspDirectives)) {
            $context->getLogger()->err(
                sprintf(
                    'Setting \'app_csp_directives\' is not set properly. CSP is not being used.'
                )
            );

            return null;
        }

        return $cspDirectives;
    }

    protected function getRandomNonce($length = 16)
    {
        return bin2hex(random_bytes($length));
    }
}
