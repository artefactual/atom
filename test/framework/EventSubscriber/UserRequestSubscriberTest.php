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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Tests\EventSubscriber;

use Atom\EventSubscriber\UserRequestSubscriber;
use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\User;
use Atom\Framework\Filter\SettingsRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @internal
 */
final class UserRequestSubscriberTest extends TestCase
{
    protected function setUp(): void
    {
        Configuration::clear();
        Configuration::set('sf_default_culture', 'en');
        Configuration::set('app_i18n_languages', ['en', 'fr']);
    }

    protected function tearDown(): void
    {
        Configuration::clear();
    }

    public function testLoadsCultureFromLegacyQueryParameter(): void
    {
        $user = new User();
        $request = $this->createRequest('/?sf_culture=fr');

        $this->createSubscriber($user)->load(
            $this->createRequestEvent($request),
        );

        self::assertSame('fr', $user->getCulture());
        self::assertSame('fr', $request->getLocale());
    }

    public function testRejectsCultureOutsideConfiguredLanguages(): void
    {
        $user = new User();
        $request = $this->createRequest(
            '/?sf_culture=de',
            ['atom_culture' => 'de'],
        );

        $this->createSubscriber($user)->load(
            $this->createRequestEvent($request),
        );

        self::assertSame('en', $user->getCulture());
        self::assertSame('en', $request->getLocale());
    }

    private function createRequest(
        string $uri,
        array $cookies = [],
    ): Request {
        $request = Request::create($uri, 'GET', [], $cookies);
        $request->setSession(new Session(new MockArraySessionStorage()));

        return $request;
    }

    private function createRequestEvent(Request $request): RequestEvent
    {
        $kernel = new class implements HttpKernelInterface {
            public function handle(
                Request $request,
                int $type = self::MAIN_REQUEST,
                bool $catch = true,
            ): \Symfony\Component\HttpFoundation\Response {
                return new \Symfony\Component\HttpFoundation\Response();
            }
        };

        return new RequestEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        );
    }

    private function createSubscriber(User $user): UserRequestSubscriber
    {
        $settings = new class implements SettingsRepository {
            public function all(string $culture): array
            {
                return ['app_i18n_languages' => ['en', 'fr']];
            }
        };

        return new UserRequestSubscriber($user, $settings);
    }
}
