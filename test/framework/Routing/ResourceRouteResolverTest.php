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

use Atom\Framework\Bridge\NotFoundException;
use Atom\Framework\Routing\ResourceClassifier;
use Atom\Framework\Routing\ResourceRepository;
use Atom\Framework\Routing\ResourceRouteResolver;
use Atom\Framework\Routing\RouteCompiler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ResourceRouteResolverTest extends TestCase
{
    public function testBindsResourceRoutesBySlug(): void
    {
        $resource = new \stdClass();
        $resolver = $this->resolver($resource, 'static_page');
        $resolved = $resolver->resolve([
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitResourceRoute',
            'slug' => 'about',
            'module' => 'staticpage',
            'action' => 'index',
        ]);

        self::assertSame($resource, $resolved->resource);
        self::assertSame('staticpage', $resolved->parameters['module']);
    }

    public function testRejectsMissingResourceRoutes(): void
    {
        $this->expectException(NotFoundException::class);

        $this->resolver()->resolve([
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitResourceRoute',
            'slug' => 'missing',
        ]);
    }

    public function testFallsThroughMissingMetadataSlugs(): void
    {
        self::assertNull($this->resolver()->resolve([
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'slug' => 'missing',
            'action' => 'index',
        ]));
    }

    public function testSelectsAssignedInformationObjectTemplate(): void
    {
        $resource = new \stdClass();
        $resolver = $this->resolver(
            $resource,
            'information_object',
            ['informationobject' => 'isad'],
            'dc',
        );
        $resolved = $resolver->resolve([
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'slug' => 'description',
            'action' => 'index',
        ]);

        self::assertSame('sfDcPlugin', $resolved->parameters['module']);
        self::assertSame($resource, $resolved->resource);
    }

    public function testRewritesDefaultMetadataModules(): void
    {
        $resolved = $this->resolver()->resolve([
            RouteCompiler::CLASS_ATTRIBUTE => 'QubitMetadataRoute',
            'module' => 'repository',
            'action' => 'copy',
        ]);

        self::assertSame('sfIsdiahPlugin', $resolved->parameters['module']);
        self::assertSame('edit', $resolved->parameters['action']);
    }

    private function resolver(
        ?object $resource = null,
        ?string $type = null,
        array $templates = [],
        false|string $informationObjectTemplate = false,
    ): ResourceRouteResolver {
        $repository = new class($resource, $templates, $informationObjectTemplate) implements ResourceRepository {
            public function __construct(
                private readonly ?object $resource,
                private readonly array $templates,
                private readonly false|string $informationObjectTemplate,
            ) {}

            public function findBySlug(string $slug): ?object
            {
                return $this->resource;
            }

            public function defaultTemplate(string $module): false|string
            {
                return $this->templates[$module] ?? false;
            }

            public function informationObjectTemplate(
                object $resource,
            ): false|string {
                return $this->informationObjectTemplate;
            }
        };
        $classifier = new class($type) implements ResourceClassifier {
            public function __construct(private readonly ?string $type) {}

            public function classify(object $resource): ?string
            {
                return $this->type;
            }
        };

        return new ResourceRouteResolver($repository, $classifier);
    }
}
