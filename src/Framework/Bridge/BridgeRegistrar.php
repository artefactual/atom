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

namespace Atom\Framework\Bridge;

final class BridgeRegistrar
{
    private const ALIASES = [
        [Configuration::class, 'sfConfig'],
        [ParameterHolder::class, 'sfParameterHolder'],
        [View::class, 'sfView'],
        [Event::class, 'sfEvent'],
        [EventDispatcher::class, 'sfEventDispatcher'],
        [Context::class, 'sfContext'],
        [OutputEscaper::class, 'sfOutputEscaper'],
        [SafeValue::class, 'sfOutputEscaperSafe'],
        [Component::class, 'sfComponent'],
        [Action::class, 'sfAction'],
        [Actions::class, 'sfActions'],
        [BridgeException::class, 'sfException'],
        [BridgeException::class, 'sfConfigurationException'],
        [BridgeException::class, 'sfControllerException'],
        [BridgeException::class, 'sfInitializationException'],
        [NotFoundException::class, 'sfError404Exception'],
        [StopException::class, 'sfStopException'],
        [ForwardException::class, 'sfForwardException'],
    ];

    private static bool $registered = false;

    public function register(): void
    {
        if (self::$registered) {
            return;
        }

        foreach (self::ALIASES as [$class, $alias]) {
            if (class_exists($alias, false)) {
                if (!is_a($alias, $class, true)) {
                    throw new BridgeException(sprintf(
                        'Cannot boot the AtoM bridge after loading "%s".',
                        $alias,
                    ));
                }

                continue;
            }

            class_alias($class, $alias);
        }

        self::$registered = true;
    }
}
