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

namespace Atom\Framework\Configuration;

use Atom\Framework\Cache\PhpArrayFileCache;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

final class HybridYamlFileLoader
{
    public function __construct(
        private readonly ?PhpArrayFileCache $cache = null,
        private readonly bool $validateDependencies = false,
    ) {}

    public function load(string $path): array
    {
        if (null === $this->cache) {
            return $this->loadUncached($path);
        }

        return $this->cache->remember(
            'hybrid-yaml-v1:'.$path,
            fn (): array => $this->loadUncached($path),
            [$path],
            $this->validateDependencies,
        );
    }

    private function loadUncached(string $path): array
    {
        if (!is_readable($path)) {
            throw new ConfigurationException(sprintf(
                'Configuration file "%s" is not readable.',
                $path,
            ));
        }

        [$returned, $contents] = $this->includeFile($path);

        if (is_array($returned)) {
            return $returned;
        }

        $contents = preg_replace(
            '/^(\s*[^#\r\n]+:\s*)(%[A-Z0-9_]+%[^\s#]*)(\s*(?:#.*)?)$/m',
            "$1'$2'$3",
            $contents,
        );

        try {
            $configuration = Yaml::parse($contents);
        } catch (ParseException $exception) {
            throw new ConfigurationException(sprintf(
                'Unable to parse configuration file "%s": %s',
                $path,
                $exception->getMessage(),
            ), previous: $exception);
        }

        if (null === $configuration) {
            return [];
        }

        if (!is_array($configuration)) {
            throw new ConfigurationException(sprintf(
                'Configuration file "%s" must contain a mapping.',
                $path,
            ));
        }

        return $configuration;
    }

    private function includeFile(string $path): array
    {
        $bufferLevel = ob_get_level();
        ob_start();

        try {
            $returned = include $path;
            $contents = ob_get_clean();
        } catch (\Throwable $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            throw new ConfigurationException(sprintf(
                'Unable to evaluate configuration file "%s": %s',
                $path,
                $exception->getMessage(),
            ), previous: $exception);
        }

        return [$returned, $contents];
    }
}
