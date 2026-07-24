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

use Atom\Framework\Database\PropelBootstrap;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class PropelRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private PropelBootstrap $bootstrap) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['initialize', 64],
        ];
    }

    public function initialize(RequestEvent $event): void
    {
        if ($event->isMainRequest()) {
            $this->bootstrap->initialize();
        }
    }
}
