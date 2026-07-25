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

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Configuration\YamlConfigHandler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class ConfigCacheTest extends TestCase
{
    private string $projectDirectory;
    private ?string $cachePath = null;

    protected function setUp(): void
    {
        Configuration::clear();
        Configuration::set('sf_environment', 'test');
        $this->projectDirectory = sys_get_temp_dir()
            .'/atom-config-cache-'.bin2hex(random_bytes(6));
        mkdir($this->projectDirectory.'/config', 0775, true);
        file_put_contents(
            $this->projectDirectory.'/config/example.yml',
            "all:\n  enabled: true\n",
        );
    }

    protected function tearDown(): void
    {
        if (null !== $this->cachePath && is_file($this->cachePath)) {
            unlink($this->cachePath);
        }

        $cacheDirectory = $this->projectDirectory
            .'/cache/qubit/test/config';

        foreach ([
            $cacheDirectory,
            dirname($cacheDirectory),
            dirname($cacheDirectory, 2),
            $this->projectDirectory.'/cache',
        ] as $directory) {
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }

        unlink($this->projectDirectory.'/config/example.yml');
        rmdir($this->projectDirectory.'/config');
        rmdir($this->projectDirectory);
        Configuration::clear();
    }

    public function testCompilesRegisteredConfiguration(): void
    {
        $configuration = new RuntimeConfiguration(
            'qubit',
            'test',
            [],
            true,
            $this->projectDirectory,
        );
        $cache = $configuration->getConfigCache();
        $cache->registerConfigHandler(
            'config/example.yml',
            ExampleConfigHandler::class,
        );
        $this->cachePath = $cache->checkConfig('config/example.yml');

        self::assertFileExists($this->cachePath);
        self::assertSame(
            ['enabled' => true],
            include $this->cachePath,
        );
    }
}

final class ExampleConfigHandler extends YamlConfigHandler
{
    public function execute($configFiles): string
    {
        return '<?php return '.var_export(
            self::flattenConfigurationWithEnvironment(
                self::parseYamls($configFiles),
            ),
            true,
        ).';';
    }
}
