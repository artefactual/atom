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

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\ForwardException;
use Atom\Framework\Bridge\User;
use Atom\Framework\Configuration\ModuleConfigurationLoader;
use Atom\Framework\Security\SecurityConfiguration;
use Atom\Framework\Security\SecurityEnforcer;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class SecurityEnforcerTest extends TestCase
{
    private SecurityEnforcer $enforcer;

    protected function setUp(): void
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
                                    edit:
                                      is_secure: true
                                      credentials:
                                        - [editor, administrator]
                                    YAML,
                            ],
                        ],
                    ],
                ],
            ],
        ]);
        $this->enforcer = new SecurityEnforcer(
            new SecurityConfiguration(new ModuleConfigurationLoader(
                'vfs://project',
                'qubit',
                [],
                'vfs://project/framework',
            )),
        );
        Configuration::clear();
        Configuration::add([
            'sf_login_module' => 'user',
            'sf_login_action' => 'login',
            'sf_secure_module' => 'admin',
            'sf_secure_action' => 'secure',
        ]);
    }

    protected function tearDown(): void
    {
        Configuration::clear();
    }

    public function testForwardsAnonymousUsersToLogin(): void
    {
        try {
            $this->enforcer->enforce(new User(), 'records', 'edit');
            self::fail('Expected a login forward.');
        } catch (ForwardException $exception) {
            self::assertSame('user', $exception->module);
            self::assertSame('login', $exception->action);
        }
    }

    public function testAcceptsNestedAlternativeCredentials(): void
    {
        $user = new User();
        $user->setAuthenticated(true);
        $user->addCredential('editor');

        self::assertSame(
            [['editor', 'administrator']],
            $this->enforcer
                ->enforce($user, 'records', 'edit')['credentials'],
        );
    }

    public function testForwardsMissingCredentialsToSecureAction(): void
    {
        $user = new User();
        $user->setAuthenticated(true);

        try {
            $this->enforcer->enforce($user, 'records', 'edit');
            self::fail('Expected a secure action forward.');
        } catch (ForwardException $exception) {
            self::assertSame('admin', $exception->module);
            self::assertSame('secure', $exception->action);
        }
    }
}
