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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

/**
 * @internal
 *
 * @coversNothing
 */
final class UserTest extends TestCase
{
    protected function setUp(): void
    {
        Configuration::clear();
        Configuration::set('sf_default_culture', 'en');
    }

    protected function tearDown(): void
    {
        Configuration::clear();
    }

    public function testPersistsLegacyUserStateInSymfonySession(): void
    {
        $session = new Session(new MockArraySessionStorage());
        $request = Request::create('/');
        $request->setSession($session);
        $first = new User();
        $first->beginRequest($request);
        $first->setAuthenticated(true);
        $first->setAttribute('item', 42, 'clipboard');
        $first->addCredential('editor');
        $first->setCulture('fr');
        $first->persist();

        $second = new User();
        $second->beginRequest($request);

        self::assertTrue($second->isAuthenticated());
        self::assertSame(42, $second->getAttribute(
            'item',
            null,
            'clipboard',
        ));
        self::assertTrue($second->hasCredential('editor'));
        self::assertSame('fr', $second->getCulture());
    }

    public function testEvaluatesNestedCredentials(): void
    {
        $user = new User();
        $user->addCredential('editor');

        self::assertInstanceOf(\Zend_Acl_Role_Interface::class, $user);
        self::assertTrue($user->hasCredential([
            ['administrator', 'editor'],
        ]));
        self::assertFalse($user->hasCredential([
            'administrator',
            'editor',
        ]));
    }
}
