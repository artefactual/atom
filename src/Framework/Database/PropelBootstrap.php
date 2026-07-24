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

namespace Atom\Framework\Database;

use Atom\Framework\Configuration\ConfigurationException;

final class PropelBootstrap
{
    private bool $initialized = false;

    public function __construct(private readonly array $databases) {}

    public function initialize(): bool
    {
        if ($this->initialized) {
            return true;
        }

        $configuration = $this->compile($this->databases);

        if ([] === $configuration['datasources']) {
            return false;
        }

        if (!class_exists(\Propel::class)) {
            throw new ConfigurationException(
                'The bundled Propel runtime could not be loaded.',
            );
        }

        \Propel::setConfiguration($configuration);
        \Propel::initialize();

        if ($configuration['instance-pooling']) {
            \Propel::enableInstancePooling();
        } else {
            \Propel::disableInstancePooling();
        }

        $this->initialized = true;

        return true;
    }

    public function compile(array $databases): array
    {
        $datasources = [];
        $default = null;
        $pooling = false;

        foreach ($databases as $name => $database) {
            if (!is_array($database)) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" must contain a mapping.',
                    $name,
                ));
            }

            $class = $database['class'] ?? 'sfPropelDatabase';

            if ('sfPropelDatabase' !== $class) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" uses unsupported class "%s".',
                    $name,
                    $class,
                ));
            }

            $parameters = $database['param'] ?? [];

            if (!is_array($parameters)) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" parameters must contain a mapping.',
                    $name,
                ));
            }

            $datasource = (string) (
                $parameters['datasource']
                ?? $parameters['name']
                ?? $name
            );
            $dsn = $parameters['dsn'] ?? null;

            if (!is_string($dsn) || '' === $dsn) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" must define a DSN.',
                    $name,
                ));
            }

            $adapter = $parameters['phptype']
                ?? strstr($dsn, ':', true);

            if (!is_string($adapter) || '' === $adapter) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" has an invalid DSN.',
                    $name,
                ));
            }

            $options = $parameters['options'] ?? [];

            if (!is_array($options)) {
                throw new ConfigurationException(sprintf(
                    'Database "%s" options must contain a mapping.',
                    $name,
                ));
            }

            if (array_key_exists('persistent', $parameters)) {
                $options['ATTR_PERSISTENT'] = [
                    'value' => (bool) $parameters['persistent'],
                ];
            }

            $datasources[$datasource] = [
                'adapter' => $adapter,
                'connection' => [
                    'dsn' => $dsn,
                    'user' => $parameters['username'] ?? null,
                    'password' => $parameters['password'] ?? null,
                    'classname' => $parameters['classname'] ?? 'PropelPDO',
                    'options' => $options,
                    'settings' => [
                        'charset' => [
                            'value' => $parameters['encoding'] ?? 'utf8mb4',
                        ],
                        'queries' => $parameters['queries'] ?? [],
                    ],
                ],
            ];
            $default ??= $datasource;
            $pooling = $pooling || (bool) (
                $parameters['pooling'] ?? false
            );
        }

        if (null !== $default) {
            $datasources['default'] = $default;
        }

        return [
            'datasources' => $datasources,
            'instance-pooling' => $pooling,
        ];
    }
}
