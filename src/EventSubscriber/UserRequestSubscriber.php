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

namespace Atom\EventSubscriber;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\PropelBridge;
use Atom\Framework\Bridge\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class UserRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private User $user) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['load', 20],
            KernelEvents::RESPONSE => ['save', -900],
        ];
    }

    public function load(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $this->user->beginRequest($request);
        $culture = trim((string) $request->headers->get(
            'X-Atom-Culture',
            '',
        ));
        $allowedCultures = Configuration::get(
            'app_i18n_languages',
            [],
        );

        if (
            '' !== $culture
            && is_array($allowedCultures)
            && in_array($culture, $allowedCultures, true)
        ) {
            $this->user->setCulture($culture);
        }

        PropelBridge::setDefaultCulture($this->user->getCulture());
        $request->setLocale($this->user->getCulture());
    }

    public function save(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->user->persist();
        $response = $event->getResponse();
        $response->headers->setCookie(Cookie::create(
            'atom_authenticated',
            $this->user->isAuthenticated() ? '1' : '0',
        )
            ->withPath('/')
            ->withSecure(true)
            ->withSameSite(Cookie::SAMESITE_STRICT));

        if ($this->user->cultureChanged()) {
            $response->headers->setCookie(Cookie::create(
                'atom_culture',
                $this->user->getCulture(),
            )
                ->withPath('/')
                ->withSecure(true)
                ->withHttpOnly(true)
                ->withSameSite(Cookie::SAMESITE_STRICT));
        }
    }
}
