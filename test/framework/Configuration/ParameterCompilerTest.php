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

namespace Atom\Tests\Framework\Configuration;

use Atom\Framework\Configuration\ParameterCompiler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ParameterCompilerTest extends TestCase
{
    public function testCompilesAtoMParameterNames(): void
    {
        $compiler = new ParameterCompiler();

        self::assertSame([
            'app_upload_limit' => -1,
            'app_cache_engine_param_host' => 'memcached',
            'app_cache_engine_param_storeCacheInfo' => true,
            'app_enabled' => true,
        ], $compiler->compile([
            '.settings' => ['upload_limit' => -1],
            'cache_engine_param' => [
                'host' => 'memcached',
                'storeCacheInfo' => true,
            ],
            'ENABLED' => true,
        ], 'APP_'));
    }
}
