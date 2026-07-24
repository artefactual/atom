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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Filter;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\Context;
use Symfony\Component\HttpFoundation\Response;

final readonly class CspFilter implements FilterHandler
{
    private const HEADERS = [
        'Content-Security-Policy',
        'Content-Security-Policy-Report-Only',
    ];

    public function process(Context $context, callable $next): Response
    {
        if (
            !Configuration::get('app_b5_theme', false)
            || $context->getConfiguration()->isDebug()
        ) {
            return $next();
        }

        $header = Configuration::get('app_csp_response_header');
        $directives = trim((string) preg_replace(
            '/\s+/',
            ' ',
            (string) Configuration::get('app_csp_directives', ''),
        ));

        if (
            !is_string($header)
            || !in_array($header, self::HEADERS, true)
            || '' === $directives
        ) {
            return $next();
        }

        $nonce = bin2hex(random_bytes(16));
        Configuration::set('csp_nonce', 'nonce='.$nonce);
        $response = $next();
        $contentType = (string) $response->headers->get('Content-Type');

        if (!preg_match('~(text/xml|application/json)~', $contentType)) {
            $response->headers->set(
                $header,
                str_replace('nonce', 'nonce-'.$nonce, $directives),
            );
        }

        return $response;
    }
}
