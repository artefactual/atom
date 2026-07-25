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

final readonly class SchemaInstaller
{
    public function install(
        string $sqlDirectory,
        string $connectionName = 'propel',
    ): void {
        $sqlDirectory = realpath($sqlDirectory) ?: throw new \RuntimeException(
            sprintf('SQL directory "%s" was not found.', $sqlDirectory),
        );
        $mapPath = $sqlDirectory.'/sqldb.map';
        $map = file($mapPath, \FILE_IGNORE_NEW_LINES);

        if (false === $map) {
            throw new \RuntimeException(sprintf(
                'Unable to read SQL map "%s".',
                $mapPath,
            ));
        }

        $databaseConnection = \Propel::getConnection($connectionName);

        foreach ($map as $line) {
            $line = trim($line);

            if ('' === $line || str_starts_with($line, '#')) {
                continue;
            }

            [$file, $mappedConnection] = array_pad(
                explode('=', $line, 2),
                2,
                null,
            );

            if (
                null === $mappedConnection
                || $connectionName !== trim($mappedConnection)
            ) {
                continue;
            }

            $path = realpath($sqlDirectory.'/'.trim($file));

            if (
                false === $path
                || !str_starts_with($path, $sqlDirectory.'/')
            ) {
                throw new \RuntimeException(sprintf(
                    'SQL file "%s" was not found in the SQL directory.',
                    trim($file),
                ));
            }

            $sql = file_get_contents($path);

            if (false === $sql) {
                throw new \RuntimeException(sprintf(
                    'Unable to read SQL file "%s".',
                    $path,
                ));
            }

            foreach ($this->statements($sql) as $statement) {
                $databaseConnection->exec($statement);
            }
        }
    }

    public function statements(string $sql): array
    {
        $statements = [];
        $statement = '';
        $quote = null;
        $length = strlen($sql);
        $lineComment = false;
        $blockComment = false;

        for ($index = 0; $index < $length; ++$index) {
            $character = $sql[$index];
            $next = $index + 1 < $length ? $sql[$index + 1] : null;

            if ($lineComment) {
                if ("\n" === $character) {
                    $lineComment = false;
                    $statement .= "\n";
                }

                continue;
            }

            if ($blockComment) {
                if ('*' === $character && '/' === $next) {
                    $blockComment = false;
                    ++$index;
                }

                continue;
            }

            if (null !== $quote) {
                $statement .= $character;

                if ('\\' === $character && null !== $next) {
                    $statement .= $next;
                    ++$index;

                    continue;
                }

                if ($quote === $character) {
                    if ($quote === $next) {
                        $statement .= $next;
                        ++$index;
                    } else {
                        $quote = null;
                    }
                }

                continue;
            }

            if ('#' === $character) {
                $lineComment = true;

                continue;
            }

            if (
                '-' === $character
                && '-' === $next
                && (
                    $index + 2 >= $length
                    || ctype_space($sql[$index + 2])
                )
            ) {
                $lineComment = true;
                ++$index;

                continue;
            }

            if ('/' === $character && '*' === $next) {
                $blockComment = true;
                ++$index;

                continue;
            }

            if (in_array($character, ["'", '"', '`'], true)) {
                $quote = $character;
                $statement .= $character;

                continue;
            }

            if (';' === $character) {
                $statement = trim($statement);

                if ('' !== $statement) {
                    $statements[] = $statement;
                }

                $statement = '';

                continue;
            }

            $statement .= $character;
        }

        $statement = trim($statement);

        if ('' !== $statement) {
            $statements[] = $statement;
        }

        return $statements;
    }
}
