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

use Atom\Framework\Configuration\ConfigurationException;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class HybridYamlFileLoaderTest extends TestCase
{
    public function testLoadsYamlWithEmbeddedPhp(): void
    {
        vfsStream::setup('root', null, [
            'settings.yml' => <<<'YAML'
                test:
                  .settings:
                    error_reporting: <?php echo E_ERROR."\n" ?>
                YAML,
        ]);

        self::assertSame([
            'test' => [
                '.settings' => [
                    'error_reporting' => E_ERROR,
                ],
            ],
        ], (new HybridYamlFileLoader())->load(
            'vfs://root/settings.yml',
        ));
    }

    public function testLoadsPhpConfigurationReturningAnArray(): void
    {
        vfsStream::setup('root', null, [
            'config.php' => <<<'PHP'
                <?php

                return ['all' => ['enabled' => true]];
                PHP,
        ]);

        self::assertSame(
            ['all' => ['enabled' => true]],
            (new HybridYamlFileLoader())->load('vfs://root/config.php'),
        );
    }

    public function testRejectsScalarConfiguration(): void
    {
        vfsStream::setup('root', null, [
            'invalid.yml' => 'not-a-mapping',
        ]);

        $this->expectException(ConfigurationException::class);
        (new HybridYamlFileLoader())->load('vfs://root/invalid.yml');
    }
}
