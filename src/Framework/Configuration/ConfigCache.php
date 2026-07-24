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

use Atom\Framework\Bridge\BridgeException;
use Atom\Framework\Bridge\RuntimeConfiguration;

final class ConfigCache
{
    private array $handlers = [];

    public function __construct(
        private readonly RuntimeConfiguration $configuration,
        private readonly string $cacheDirectory,
    ) {}

    public function registerConfigHandler(
        string $configPath,
        string $handler,
    ): void {
        if (!is_a($handler, ConfigHandler::class, true)) {
            throw new BridgeException(sprintf(
                'Configuration handler "%s" must extend "%s".',
                $handler,
                ConfigHandler::class,
            ));
        }

        $this->handlers[$configPath] = $handler;
    }

    public function checkConfig(
        string $configPath,
        bool $optional = false,
    ): false|string {
        $configFiles = $this->configuration->getConfigPaths($configPath);

        if ([] === $configFiles) {
            if ($optional) {
                return false;
            }

            throw new BridgeException(sprintf(
                'Configuration "%s" was not found.',
                $configPath,
            ));
        }

        if (!isset($this->handlers[$configPath])) {
            throw new BridgeException(sprintf(
                'No handler is registered for configuration "%s".',
                $configPath,
            ));
        }

        $handlerClass = $this->handlers[$configPath];
        $cachePath = $this->cachePath($configPath, $handlerClass);

        if (!$this->isFresh($cachePath, $configFiles, $handlerClass)) {
            $this->write(
                $cachePath,
                (new $handlerClass())->execute($configFiles),
            );
        }

        return $cachePath;
    }

    private function cachePath(
        string $configPath,
        string $handler,
    ): string {
        return rtrim($this->cacheDirectory, '/\\')
            .'/'.hash('sha256', $handler."\0".$configPath).'.php';
    }

    private function isFresh(
        string $cachePath,
        array $configFiles,
        string $handler,
    ): bool {
        if (!is_file($cachePath)) {
            return false;
        }

        $cacheTime = filemtime($cachePath);
        $handlerFile = (new \ReflectionClass($handler))->getFileName();
        $dependencies = $configFiles;

        if (is_string($handlerFile)) {
            $dependencies[] = $handlerFile;
        }

        foreach ($dependencies as $dependency) {
            if (filemtime($dependency) > $cacheTime) {
                return false;
            }
        }

        return true;
    }

    private function write(string $cachePath, string $contents): void
    {
        $directory = dirname($cachePath);

        if (
            !is_dir($directory)
            && !mkdir($directory, 0775, true)
            && !is_dir($directory)
        ) {
            throw new BridgeException(sprintf(
                'Unable to create configuration cache directory "%s".',
                $directory,
            ));
        }

        $temporaryPath = tempnam($directory, 'config-');

        if (
            false === $temporaryPath
            || false === file_put_contents(
                $temporaryPath,
                $contents,
                \LOCK_EX,
            )
            || !rename($temporaryPath, $cachePath)
        ) {
            if (is_string($temporaryPath) && is_file($temporaryPath)) {
                unlink($temporaryPath);
            }

            throw new BridgeException(sprintf(
                'Unable to write configuration cache file "%s".',
                $cachePath,
            ));
        }
    }
}
