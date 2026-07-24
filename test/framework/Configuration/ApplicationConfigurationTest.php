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

use Atom\Framework\Configuration\ApplicationConfiguration;
use Atom\Framework\Configuration\ConfigurationMerger;
use Atom\Framework\Configuration\ConfigurationPathResolver;
use Atom\Framework\Configuration\ConstantReplacer;
use Atom\Framework\Configuration\HybridYamlFileLoader;
use Atom\Framework\Configuration\ParameterCompiler;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ApplicationConfigurationTest extends TestCase
{
    public function testLoadsLayeredConfigurationAsParameters(): void
    {
        vfsStream::setup('project', null, [
            'framework' => [
                'config' => [
                    'app.yml' => <<<'YAML'
                        default:
                          shared: framework
                          nested:
                            first: framework
                            overridden: framework
                        YAML,
                ],
            ],
            'plugins' => [
                'FirstPlugin' => [
                    'config' => [
                        'app.yml' => <<<'YAML'
                            all:
                              shared: plugin
                              nested:
                                overridden: plugin
                                plugin: true
                            YAML,
                    ],
                ],
            ],
            'config' => [
                'app.yml' => <<<'YAML'
                    all:
                      shared: project
                      path: "%SF_ROOT_DIR%/data"
                      nested:
                        project: true
                    YAML,
            ],
            'apps' => [
                'qubit' => [
                    'config' => [
                        'app.yml' => <<<'YAML'
                            all:
                              shared: application
                              nested:
                                application: true
                            test:
                              shared: test
                            YAML,
                    ],
                ],
            ],
        ]);

        $configuration = new ApplicationConfiguration(
            new ConfigurationPathResolver(
                'vfs://project',
                'qubit',
                ['FirstPlugin'],
                'vfs://project/framework',
            ),
            new HybridYamlFileLoader(),
            new ConfigurationMerger(),
            new ConstantReplacer(),
            new ParameterCompiler(),
            'test',
            ['sf_root_dir' => '/srv/atom'],
        );

        self::assertSame([
            'app_shared' => 'test',
            'app_nested_first' => 'framework',
            'app_nested_overridden' => 'plugin',
            'app_nested_plugin' => true,
            'app_nested_project' => true,
            'app_nested_application' => true,
            'app_path' => '/srv/atom/data',
        ], $configuration->parameters('config/app.yml', 'APP_'));
    }
}
