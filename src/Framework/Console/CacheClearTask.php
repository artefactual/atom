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

namespace Atom\Framework\Console;

use Atom\Framework\Bridge\RuntimeConfiguration;
use Symfony\Component\Filesystem\Filesystem;

class CacheClearTask extends Task
{
    protected function configure(): void
    {
        $this->addOptions([
            new CommandOption(
                'app',
                null,
                CommandOption::PARAMETER_OPTIONAL,
                'The application name',
            ),
            new CommandOption(
                'env',
                null,
                CommandOption::PARAMETER_OPTIONAL,
                'The environment',
            ),
            new CommandOption(
                'type',
                null,
                CommandOption::PARAMETER_OPTIONAL,
                'The cache type',
                'all',
            ),
        ]);
        $this->aliases = ['cc'];
        $this->namespace = 'cache';
        $this->name = 'clear';
        $this->briefDescription = 'Clears the AtoM application cache';
    }

    protected function execute($arguments = [], $options = []): int
    {
        $configuration = $this->configuration
            ?? RuntimeConfiguration::getActive();
        $cacheDirectory = $configuration->getRootDir().'/cache';

        if (!is_dir($cacheDirectory)) {
            return 0;
        }

        $application = $this->safeSegment($options['app'] ?? null);
        $environment = $this->safeSegment($options['env'] ?? null);
        $type = (string) ($options['type'] ?? 'all');
        $subdirectory = match ($type) {
            'all' => null,
            'config', 'i18n', 'template' => $type,
            'routing', 'module' => 'symfony',
            default => throw new \InvalidArgumentException(sprintf(
                'Unknown cache type "%s".',
                $type,
            )),
        };

        $targets = $this->targets(
            $cacheDirectory,
            $application,
            $environment,
            $subdirectory,
        );
        $filesystem = new Filesystem();
        $deferred = [];

        foreach ($targets as $target) {
            if (!is_dir($target)) {
                continue;
            }

            if ('cli' !== \PHP_SAPI && 'symfony' === basename($target)) {
                $deferred[] = $target;

                continue;
            }

            $items = iterator_to_array(
                new \FilesystemIterator(
                    $target,
                    \FilesystemIterator::SKIP_DOTS,
                ),
                false,
            );

            foreach ($items as $item) {
                if (
                    'cli' !== \PHP_SAPI
                    && 'symfony' === $item->getBasename()
                ) {
                    $deferred[] = $item->getPathname();

                    continue;
                }

                $filesystem->remove($item->getPathname());
            }
        }

        if ([] !== $deferred) {
            register_shutdown_function(static function () use ($deferred) {
                (new Filesystem())->remove($deferred);
            });
        }

        return 0;
    }

    private function safeSegment(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (
            !is_string($value)
            || 1 !== preg_match('/^[a-z0-9_.-]+$/i', $value)
        ) {
            throw new \InvalidArgumentException(
                'Cache path selectors must be safe names.',
            );
        }

        return $value;
    }

    private function targets(
        string $cacheDirectory,
        ?string $application,
        ?string $environment,
        ?string $subdirectory,
    ): array {
        if (null !== $application) {
            $applications = [$cacheDirectory.'/'.$application];
        } else {
            $applications = glob($cacheDirectory.'/*', \GLOB_ONLYDIR) ?: [];
            $applications = array_values(array_filter(
                $applications,
                static fn (string $path): bool => 'sessions'
                    !== basename($path),
            ));
        }

        $targets = [];

        foreach ($applications as $applicationDirectory) {
            if (null !== $environment) {
                $environments = [
                    $applicationDirectory.'/'.$environment,
                ];
            } else {
                $environments = glob(
                    $applicationDirectory.'/*',
                    \GLOB_ONLYDIR,
                ) ?: [];
            }

            foreach ($environments as $environmentDirectory) {
                $targets[] = null === $subdirectory
                    ? $environmentDirectory
                    : $environmentDirectory.'/'.$subdirectory;
            }
        }

        return $targets;
    }
}
