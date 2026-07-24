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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\RequestAdapter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 *
 * @coversNothing
 */
final class RequestAdapterTest extends TestCase
{
    public function testProvidesLegacyArrayAccessToParameters(): void
    {
        $request = new Request([], ['email' => 'test@example.com']);
        $adapter = new RequestAdapter($request);

        self::assertSame('test@example.com', $adapter['email']);

        $adapter['next'] = '/';
        unset($adapter['email']);

        self::assertSame('/', $adapter->next);
        self::assertFalse(isset($adapter['email']));
    }
}
