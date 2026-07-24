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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class SslRequirementFilter implements FilterHandler
{
    public function process(Context $context, callable $next): Response
    {
        $request = $context->getRequest();

        if (
            $context->getConfiguration()->isDebug()
            || $request->getSymfonyRequest()->isSecure()
            || !Configuration::get('app_require_ssl_admin', false)
        ) {
            return $next();
        }

        if (
            $context->getUser()->isAuthenticated()
            || (
                'user' === $request->getParameter('module')
                && 'login' === $request->getParameter('action')
            )
        ) {
            $secureUrl = preg_replace(
                '/^http:/i',
                'https:',
                $request->getUri(),
            );

            return new RedirectResponse((string) $secureUrl);
        }

        return $next();
    }
}
