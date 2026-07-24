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

namespace Atom\Framework\Autoload;

final class ClassMapBuilder
{
    public function build(iterable $directories): array
    {
        $classMap = [];

        foreach ($directories as $directory) {
            if (!is_dir($directory)) {
                continue;
            }

            try {
                $files = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $directory,
                        \FilesystemIterator::SKIP_DOTS,
                    ),
                );
            } catch (\UnexpectedValueException $exception) {
                throw new AutoloadException(sprintf(
                    'Unable to scan class directory "%s".',
                    $directory,
                ), previous: $exception);
            }

            foreach ($files as $file) {
                if (
                    !$file->isFile()
                    || 'php' !== strtolower($file->getExtension())
                ) {
                    continue;
                }

                foreach ($this->classesInFile($file->getPathname()) as $class) {
                    $classMap[strtolower($class)] = $file->getPathname();
                }
            }
        }

        return $classMap;
    }

    private function classesInFile(string $path): array
    {
        $contents = file_get_contents($path);

        if (false === $contents) {
            throw new AutoloadException(sprintf(
                'Unable to read class file "%s".',
                $path,
            ));
        }

        $tokens = token_get_all($contents);
        $namespace = '';
        $classes = [];
        $previousToken = null;

        foreach ($tokens as $index => $token) {
            if (!is_array($token)) {
                if (!ctype_space($token)) {
                    $previousToken = $token;
                }

                continue;
            }

            if (\T_NAMESPACE === $token[0]) {
                $namespace = $this->namespaceAfter($tokens, $index);
                $previousToken = $token[0];

                continue;
            }

            if (!in_array($token[0], [
                \T_CLASS,
                \T_INTERFACE,
                \T_TRAIT,
                \T_ENUM,
            ], true)) {
                if (!in_array($token[0], [
                    \T_WHITESPACE,
                    \T_COMMENT,
                    \T_DOC_COMMENT,
                ], true)) {
                    $previousToken = $token[0];
                }

                continue;
            }

            if (
                \T_CLASS === $token[0]
                && in_array($previousToken, [\T_NEW, \T_DOUBLE_COLON], true)
            ) {
                $previousToken = $token[0];

                continue;
            }

            $name = $this->classNameAfter($tokens, $index);

            if (null !== $name) {
                $classes[] = '' === $namespace
                    ? $name
                    : $namespace.'\\'.$name;
            }

            $previousToken = $token[0];
        }

        return $classes;
    }

    private function namespaceAfter(array $tokens, int $index): string
    {
        $namespace = '';

        for (++$index, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];

            if (';' === $token || '{' === $token) {
                break;
            }

            if (
                is_array($token)
                && in_array($token[0], [
                    \T_STRING,
                    \T_NAME_QUALIFIED,
                    \T_NAME_FULLY_QUALIFIED,
                    \T_NS_SEPARATOR,
                ], true)
            ) {
                $namespace .= $token[1];
            }
        }

        return trim($namespace, '\\');
    }

    private function classNameAfter(array $tokens, int $index): ?string
    {
        for (++$index, $count = count($tokens); $index < $count; ++$index) {
            $token = $tokens[$index];

            if (is_array($token) && \T_STRING === $token[0]) {
                return $token[1];
            }

            if ('{' === $token || '(' === $token) {
                return null;
            }
        }

        return null;
    }
}
