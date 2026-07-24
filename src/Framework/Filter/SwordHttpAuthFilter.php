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

use Atom\Framework\Bridge\Context;
use Symfony\Component\HttpFoundation\Response;

final readonly class SwordHttpAuthFilter implements FilterHandler
{
    public function process(Context $context, callable $next): Response
    {
        $request = $context->getRequest()->getSymfonyRequest();
        $username = $request->getUser();
        $password = $request->getPassword();

        if (
            null === $username
            || null === $password
            || !$context
                ->getUser()
                ->authenticateWithBasicAuth($username, $password)
        ) {
            return new Response('', Response::HTTP_UNAUTHORIZED, [
                'WWW-Authenticate' => 'Basic realm="Secure area"',
            ]);
        }

        return $next();
    }
}
