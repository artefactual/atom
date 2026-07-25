<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Translation;

final class TranslationStore
{
    private array $catalogues = [];

    public function __construct(
        private readonly array $directories,
        private readonly string $culture,
        private readonly XliffFile $xliff = new XliffFile(),
    ) {}

    public function find(string $source): ?array
    {
        foreach ($this->cataloguePaths() as $path) {
            $messages = $this->messages($path);

            if (isset($messages[$source])) {
                return [
                    'path' => $path,
                    ...$messages[$source],
                ];
            }
        }

        return null;
    }

    public function update(string $source, string $target): bool
    {
        $message = $this->find($source);

        if (null === $message) {
            return false;
        }

        $path = $message['path'];

        if (!$this->xliff->updateTarget($path, $source, $target)) {
            return false;
        }

        $this->catalogues[$path][$source]['target'] = $target;

        return true;
    }

    private function cataloguePaths(): array
    {
        $paths = [];

        foreach ($this->directories as $directory) {
            $path = rtrim((string) $directory, '/\\')
                .'/'.$this->culture.'/messages.xml';

            if (is_readable($path)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    private function messages(string $path): array
    {
        return $this->catalogues[$path] ??= $this->xliff->read($path);
    }
}
