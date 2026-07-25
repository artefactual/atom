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

namespace Atom\Tests\Framework\Security;

use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Security\SecurityConfiguration;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SecurityConfigurationTest extends TestCase
{
    public function testMergesDefaultModuleAndActionSecurity(): void
    {
        vfsStream::setup('project', null, [
            'framework' => [
                'config' => [
                    'security.yml' => <<<'YAML'
                        default:
                          is_secure: false
                        YAML,
                ],
            ],
            'apps' => [
                'qubit' => [
                    'modules' => [
                        'records' => [
                            'config' => [
                                'security.yml' => <<<'YAML'
                                    all:
                                      is_secure: true
                                    browse:
                                      credentials:
                                        - [editor, administrator]
                                    public:
                                      is_secure: false
                                    YAML,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $configuration = new SecurityConfiguration(
            new ModuleConfigurationLoader(
                'vfs://project',
                'qubit',
                [],
                'vfs://project/framework',
            ),
        );

        self::assertSame([
            'is_secure' => true,
            'credentials' => [['editor', 'administrator']],
        ], $configuration->forAction('records', 'browse'));
        self::assertSame(
            ['is_secure' => false],
            $configuration->forAction('records', 'public'),
        );
    }
}
