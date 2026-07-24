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

namespace Atom\Framework\Bridge;

use Symfony\Component\HttpFoundation\RedirectResponse;

final readonly class ControllerProxy
{
    public function __construct(private Context $context) {}

    public function forward(string $module, string $action): never
    {
        throw new ForwardException($module, $action);
    }

    public function redirect(
        array|string $url,
        int $delay = 0,
        int $statusCode = 302,
    ): never {
        throw new StopException(new RedirectResponse(
            $this->context->urlFor($url),
            $statusCode,
        ));
    }

    public function inCLI(): bool
    {
        return \PHP_SAPI === 'cli';
    }
}
