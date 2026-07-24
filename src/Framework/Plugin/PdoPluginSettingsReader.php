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

namespace Atom\Framework\Plugin;

final readonly class PdoPluginSettingsReader implements PluginSettingsReader
{
    private \Closure $connectionFactory;

    public function __construct(
        private array $databases,
        ?callable $connectionFactory = null,
    ) {
        $this->connectionFactory = null === $connectionFactory
            ? self::connect(...)
            : \Closure::fromCallable($connectionFactory);
    }

    public function read(): ?array
    {
        $parameters = $this->connectionParameters();

        if (null === $parameters) {
            return null;
        }

        try {
            $connection = ($this->connectionFactory)($parameters);
            $statement = $connection->query(
                'SELECT translation.value
                FROM setting
                INNER JOIN setting_i18n translation
                    ON translation.id = setting.id
                    AND translation.culture = setting.source_culture
                WHERE setting.name = \'plugins\'
                LIMIT 1',
            );
            $value = false === $statement ? false : $statement->fetchColumn();
        } catch (\PDOException) {
            return null;
        }

        if (false === $value) {
            return null;
        }

        $plugins = @unserialize(
            (string) $value,
            ['allowed_classes' => false],
        );

        if (!is_array($plugins)) {
            throw new PluginException(
                'The stored AtoM plugin setting is not a serialized list.',
            );
        }

        return $plugins;
    }

    private function connectionParameters(): ?array
    {
        foreach ($this->databases as $database) {
            if (!is_array($database)) {
                continue;
            }

            $parameters = $database['param'] ?? null;

            if (
                is_array($parameters)
                && isset($parameters['dsn'])
                && is_string($parameters['dsn'])
            ) {
                return $parameters;
            }
        }

        return null;
    }

    private static function connect(array $parameters): \PDO
    {
        return new \PDO(
            $parameters['dsn'],
            $parameters['username'] ?? null,
            $parameters['password'] ?? null,
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_TIMEOUT => 2,
            ],
        );
    }
}
