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

abstract class OutputEscaper
{
    /**
     * @var array<class-string>
     */
    private static array $safeClasses = [];

    public function __construct(
        protected string $escapingMethod,
        protected mixed $value,
    ) {}

    public function __get(string $name): mixed
    {
        return self::escape(
            $this->escapingMethod,
            $this->value->{$name},
        );
    }

    public static function escape(
        string $escapingMethod,
        mixed $value,
    ): mixed {
        if (null === $value) {
            return null;
        }

        if (is_scalar($value)) {
            return $escapingMethod($value);
        }

        if (is_array($value)) {
            return new OutputEscaperArrayDecorator(
                $escapingMethod,
                $value,
            );
        }

        if (is_object($value)) {
            if ($value instanceof self) {
                $copy = clone $value;
                $copy->escapingMethod = $escapingMethod;

                return $copy;
            }

            if ($value instanceof SafeValue) {
                return $value->getValue();
            }

            if (self::isClassMarkedAsSafe($value::class)) {
                return $value;
            }

            if ($value instanceof \Traversable) {
                return new OutputEscaperIteratorDecorator(
                    $escapingMethod,
                    $value,
                );
            }

            return new OutputEscaperObjectDecorator(
                $escapingMethod,
                $value,
            );
        }

        throw new \InvalidArgumentException(sprintf(
            'Unable to escape value "%s".',
            var_export($value, true),
        ));
    }

    public static function unescape(mixed $value): mixed
    {
        if (null === $value || is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return html_entity_decode(
                $value,
                \ENT_QUOTES,
                (string) Configuration::get('sf_charset', 'UTF-8'),
            );
        }

        if (is_scalar($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(self::unescape(...), $value);
        }

        if ($value instanceof self) {
            return $value->getRawValue();
        }

        if ($value instanceof SafeValue) {
            return $value->getValue();
        }

        return $value;
    }

    public static function isClassMarkedAsSafe(string $class): bool
    {
        foreach (self::$safeClasses as $safeClass) {
            if ($class === $safeClass || is_subclass_of($class, $safeClass)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array<class-string> $classes
     */
    public static function markClassesAsSafe(array $classes): void
    {
        self::$safeClasses = array_values(array_unique([
            ...self::$safeClasses,
            ...$classes,
        ]));
    }

    public static function markClassAsSafe(string $class): void
    {
        self::markClassesAsSafe([$class]);
    }

    public function getRawValue(): mixed
    {
        return $this->value;
    }
}
