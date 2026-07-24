<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Intl\Scripts;

final readonly class CultureInfo
{
    private const RTL_LANGUAGES = ['ar', 'fa', 'he', 'ur'];

    private string $culture;

    public function __construct(?string $culture = 'en')
    {
        $this->culture = '' === (string) $culture
            ? 'en'
            : (string) $culture;
    }

    public function __get(string $name): mixed
    {
        $method = 'get'.ucfirst($name);

        if (!method_exists($this, $method)) {
            throw new BridgeException(sprintf(
                'Culture property "%s" does not exist.',
                $name,
            ));
        }

        return $this->{$method}();
    }

    public function __toString(): string
    {
        return $this->culture;
    }

    public static function getInstance(?string $culture = 'en'): self
    {
        static $instances = [];
        $culture = '' === (string) $culture ? 'en' : (string) $culture;

        return $instances[$culture] ??= new self($culture);
    }

    public static function validCulture(mixed $culture): bool
    {
        if (!is_string($culture) || '' === $culture) {
            return false;
        }

        $normalized = str_replace('@valencia', '', $culture);

        return Locales::exists($normalized);
    }

    public function getName(): string
    {
        return Locales::getName($this->culture, $this->displayCulture())
            ?: $this->culture;
    }

    public function getDirection(): string
    {
        $language = strtolower(strtok($this->culture, '_@') ?: 'en');

        return in_array($language, self::RTL_LANGUAGES, true)
            ? 'rtl'
            : 'ltr';
    }

    public function getCountries(?array $countries = null): array
    {
        $names = Countries::getNames($this->displayCulture());

        return null === $countries
            ? $names
            : array_intersect_key($names, array_flip($countries));
    }

    public function getCountry(string $country): string
    {
        return Countries::getName(
            strtoupper($country),
            $this->displayCulture(),
        ) ?: $country;
    }

    public function getLanguages(?array $languages = null): array
    {
        $names = Languages::getNames($this->displayCulture());

        return null === $languages
            ? $names
            : array_intersect_key($names, array_flip($languages));
    }

    public function getLanguage(string $language): string
    {
        return Languages::getName(
            strtolower($language),
            $this->displayCulture(),
        ) ?: $language;
    }

    public function getScripts(): array
    {
        return Scripts::getNames($this->displayCulture());
    }

    private function displayCulture(): string
    {
        return str_replace('@valencia', '', $this->culture);
    }
}
