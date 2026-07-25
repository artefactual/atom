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

namespace Atom\Framework\Database;

use Atom\Framework\Utility\Inflector;
use Atom\Framework\Utility\Yaml;

final class FixtureLoader
{
    private array $references = [];
    private \DatabaseMap $databaseMap;

    public function __construct(private readonly string $projectDirectory) {}

    public function load(
        array|string $paths,
        string $connectionName = 'propel',
    ): void {
        $files = $this->files((array) $paths);
        $this->loadTableMaps($connectionName);
        $connection = \Propel::getConnection($connectionName);
        $this->references = [];
        $connection->beginTransaction();

        try {
            foreach ($files as $file) {
                $this->loadArray(Yaml::load($file), $connection);
            }

            $connection->commit();
        } catch (\Throwable $exception) {
            $connection->rollBack();

            throw $exception;
        }
    }

    public function files(array $paths): array
    {
        $files = [];

        foreach ($paths as $path) {
            if (!is_string($path) || !is_readable($path)) {
                continue;
            }

            if (is_file($path)) {
                $files[] = realpath($path) ?: $path;

                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $path,
                    \FilesystemIterator::SKIP_DOTS,
                ),
            );

            foreach ($iterator as $file) {
                if (
                    $file->isFile()
                    && 'yml' === strtolower($file->getExtension())
                ) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $files = array_values(array_unique($files));
        sort($files);

        return $files;
    }

    private function loadArray(
        mixed $fixtures,
        \PropelPDO $connection,
    ): void {
        if (null === $fixtures) {
            return;
        }

        if (!is_array($fixtures)) {
            throw new \InvalidArgumentException(
                'Fixture files must contain a mapping.',
            );
        }

        foreach ($fixtures as $class => $records) {
            $class = trim((string) $class);

            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unknown fixture class "%s".',
                    $class,
                ));
            }

            $table = constant($class.'::TABLE_NAME');
            $tableMap = $this->databaseMap->getTable($table);

            if (!is_array($records)) {
                continue;
            }

            foreach ($records as $reference => $values) {
                if (!is_array($values)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Fixture record "%s" in "%s" must contain a mapping.',
                        $reference,
                        $class,
                    ));
                }

                $object = new $class();

                foreach ($values as $name => $value) {
                    $column = null;

                    try {
                        $column = $tableMap->getColumn($name);
                    } catch (\PropelException) {
                    }

                    if (
                        null !== $column
                        && $column->isForeignKey()
                        && is_string($value)
                        && isset($this->references[$value])
                    ) {
                        $value = $this->references[$value]->getPrimaryKey();
                    }

                    $setter = 'set'.Inflector::camelize((string) $name);

                    if (!is_callable([$object, $setter])) {
                        continue;
                    }

                    if (is_array($value)) {
                        foreach ($value as $culture => $translation) {
                            $object->{$setter}(
                                $translation,
                                ['culture' => $culture],
                            );
                        }
                    } else {
                        $object->{$setter}($value);
                    }
                }

                $object->save($connection);

                if (method_exists($object, 'getPrimaryKey')) {
                    $this->references[$reference] = $object;
                }
            }
        }
    }

    private function loadTableMaps(string $connectionName): void
    {
        $this->databaseMap = \Propel::getDatabaseMap($connectionName);
        $directories = [$this->projectDirectory.'/lib/model/map'];
        $plugins = glob(
            $this->projectDirectory.'/plugins/*Plugin',
            \GLOB_ONLYDIR,
        ) ?: [];

        foreach ($plugins as $plugin) {
            $directories[] = $plugin.'/lib/model/map';
        }

        foreach ($directories as $directory) {
            foreach (glob($directory.'/*TableMap.php') ?: [] as $file) {
                $class = basename($file, '.php');

                if (class_exists($class)) {
                    $this->databaseMap->addTableFromMapClass($class);
                }
            }
        }
    }
}
