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

final readonly class IpLimitFilter implements FilterHandler
{
    private const LOGOUT_MODULES = ['user', 'oidc', 'cas'];

    public function __construct(private IpRangeMatcher $matcher) {}

    public function process(Context $context, callable $next): Response
    {
        $request = $context->getRequest();
        $limits = array_filter(array_map(
            trim(...),
            explode(
                ';',
                (string) Configuration::get('app_limit_admin_ip', ''),
            ),
        ), static fn (string $limit): bool => '' !== $limit);

        if (
            $context->getConfiguration()->isDebug()
            || [] === $limits
            || (
                in_array(
                    $request->getParameter('module'),
                    self::LOGOUT_MODULES,
                    true,
                )
                && 'logout' === $request->getParameter('action')
            )
            || !$context->getUser()->isAuthenticated()
        ) {
            return $next();
        }

        $address = (string) (
            $request->getSymfonyRequest()->getClientIp() ?? ''
        );

        if (!$this->matcher->matches($address, $limits)) {
            $request->setParameter(
                'module',
                Configuration::get('sf_secure_module', 'admin'),
            );
            $request->setParameter(
                'action',
                Configuration::get('sf_secure_action', 'secure'),
            );
        }

        return $next();
    }
}
