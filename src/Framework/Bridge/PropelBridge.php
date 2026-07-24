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

final class PropelBridge
{
    private static string $defaultCulture = 'en';

    public static function initialize(
        EventDispatcher $dispatcher,
        ?string $culture = null,
    ): void {
        if (null !== $culture) {
            self::setDefaultCulture($culture);
        } elseif (Context::hasInstance()) {
            self::setDefaultCulture(
                Context::getInstance()->getUser()->getCulture(),
            );
        }
    }

    public static function setDefaultCulture(string $culture): void
    {
        self::$defaultCulture = $culture;
    }

    public static function getDefaultCulture(): string
    {
        return self::$defaultCulture;
    }

    public static function listenToChangeCultureEvent(Event $event): void
    {
        self::setDefaultCulture((string) $event['culture']);
    }

    public static function import(string $path): string
    {
        return \Propel::importClass($path);
    }

    public static function importClass(string $path): string
    {
        return \Propel::importClass($path);
    }
}
