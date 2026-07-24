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

use Atom\Framework\Module\ActionLocator;
use Atom\Framework\Module\ModuleDirectories;
use Atom\Framework\Plugin\PluginRegistry;
use Atom\Framework\Plugin\PluginSettingsReader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class PluginRegistryTest extends TestCase
{
    public function testReturnsInstalledPluginsInApplicationOrder(): void
    {
        $projectDirectory = dirname(__DIR__, 3);

        self::assertSame([
            'arDominionB5Plugin',
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
            'sfDcPlugin',
            'sfEacPlugin',
            'sfEadPlugin',
            'sfIsaarPlugin',
            'sfIsadPlugin',
            'arDacsPlugin',
            'sfIsdfPlugin',
            'sfIsdiahPlugin',
            'sfModsPlugin',
            'sfRadPlugin',
            'sfSkosPlugin',
        ], (new PluginRegistry($projectDirectory, false))->enabled());
    }

    public function testAppendsOidcPluginWhenActivated(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $settings = new class implements PluginSettingsReader {
            public function read(): ?array
            {
                return [];
            }
        };
        $plugins = (new PluginRegistry(
            $projectDirectory,
            true,
            $settings,
        ))->enabled();

        self::assertSame('arOidcPlugin', $plugins[array_key_last($plugins)]);
    }

    public function testUsesConfiguredPluginsAndIgnoresMissingOnes(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $settings = new class implements PluginSettingsReader {
            public function read(): ?array
            {
                return [
                    'arRestApiPlugin',
                    'missingPlugin',
                    'arDominionB5Plugin',
                ];
            }
        };

        self::assertSame([
            'arDominionB5Plugin',
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
            'arRestApiPlugin',
        ], (new PluginRegistry(
            $projectDirectory,
            false,
            $settings,
        ))->enabled());
    }

    public function testMakesMetadataPluginActionsDiscoverable(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $plugins = (new PluginRegistry(
            $projectDirectory,
            false,
        ))->enabled();
        $locator = new ActionLocator(new ModuleDirectories(
            $projectDirectory,
            'qubit',
            $plugins,
        ));
        $action = $locator->find('sfIsadPlugin', 'index');

        self::assertNotNull($action);
        self::assertSame(
            $projectDirectory
                .'/plugins/sfIsadPlugin/modules/sfIsadPlugin'
                .'/actions/indexAction.class.php',
            $action->path,
        );
    }
}
