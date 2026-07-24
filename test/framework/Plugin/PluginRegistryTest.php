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

namespace Atom\Tests\Framework\Plugin;

use Atom\Framework\Plugin\PluginRegistry;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PluginRegistryTest extends TestCase
{
    public function testReturnsCorePluginsInApplicationOrder(): void
    {
        self::assertSame([
            'qbAclPlugin',
            'qtAccessionPlugin',
            'sfDrupalPlugin',
            'sfHistoryPlugin',
            'arElasticSearchPlugin',
            'sfPropelPlugin',
            'sfThumbnailPlugin',
            'sfTranslatePlugin',
            'sfWebBrowserPlugin',
            'sfPluginAdminPlugin',
        ], (new PluginRegistry('/srv/atom', false))->enabled());
    }

    public function testAppendsOidcPluginWhenActivated(): void
    {
        $plugins = (new PluginRegistry('/srv/atom', true))->enabled();

        self::assertSame('arOidcPlugin', $plugins[array_key_last($plugins)]);
    }
}
