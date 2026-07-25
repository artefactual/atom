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

namespace Atom\Tests\Framework\Routing;

use Atom\Framework\Configuration\ApplicationConfiguration;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use Atom\Framework\Configuration\ConstantReplacer;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use Atom\Framework\Configuration\ParameterCompiler;
use Atom\Framework\Routing\RouteCompiler;
use Atom\Framework\Routing\RouteConfigurationLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;

/**
 * @internal
 */
final class RouteConfigurationLoaderTest extends TestCase
{
    public function testLoadsAndMatchesAtoMRoutes(): void
    {
        $routes = $this->loader()->load();

        self::assertCount(17, $routes);

        $matcher = new UrlMatcher($routes, new RequestContext());

        self::assertSame([
            'module' => 'informationobject',
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitResourceRoute',
            'slug' => 'peanut',
            'action' => 'fileList',
            '_route' => 'informationobject/action',
        ], $matcher->match('/peanut/fileList'));

        self::assertSame([
            'module' => 'peanut',
            'action' => 'delete',
            '_route' => 'default',
        ], $matcher->match('/peanut/delete'));

        self::assertSame([
            'module' => 'digitalobject',
            'action' => 'view',
            '_star' => 'path/to/file',
            '_route' => 'upload',
        ], $matcher->match('/uploads/r/path/to/file'));
    }

    public function testGeneratesAtoMRouteUrls(): void
    {
        $generator = new UrlGenerator(
            $this->loader()->load(),
            new RequestContext(),
        );

        self::assertSame(
            '/record;isad',
            $generator->generate('slug;template', [
                'slug' => 'record',
                'template' => 'isad',
            ]),
        );
        self::assertSame(
            '/actor/edit/id/42',
            $generator->generate('id/default', [
                'module' => 'actor',
                'action' => 'edit',
                'id' => 42,
            ]),
        );
    }

    private function loader(): RouteConfigurationLoader
    {
        $projectDirectory = dirname(__DIR__, 3);

        return new RouteConfigurationLoader(
            new ApplicationConfiguration(
                new ConfigurationPathResolver(
                    $projectDirectory,
                    'qubit',
                ),
                new HybridYamlFileLoader(),
                new ConfigurationMerger(),
                new ConstantReplacer(),
                new ParameterCompiler(),
                'test',
            ),
            new RouteCompiler(),
        );
    }
}
