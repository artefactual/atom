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

namespace Atom\Framework\Translation;

use Atom\Framework\Utility\Finder;
use Atom\Framework\Utility\Yaml;

final readonly class TranslationCatalogue
{
    public function __construct(
        private string $projectDirectory,
        private string $application = 'qubit',
        private XliffFile $xliff = new XliffFile(),
        private PhpMessageExtractor $extractor = new PhpMessageExtractor(),
    ) {}

    public function readCulture(string $culture): array
    {
        $messages = [];

        foreach ($this->translationDirectories() as $directory) {
            $path = $directory.'/'.$culture.'/messages.xml';

            if (!is_readable($path)) {
                continue;
            }

            foreach ($this->xliff->read($path) as $source => $message) {
                if (
                    !isset($messages[$source])
                    || (
                        '' === $messages[$source]['target']
                        && '' !== $message['target']
                    )
                ) {
                    $messages[$source] = $message;
                }
            }
        }

        return $messages;
    }

    public function extractMessages(): array
    {
        $messages = [];

        foreach ($this->sourceDirectories() as $directory) {
            foreach (Finder::type('file')->name('*.php')->in($directory) as $file) {
                $contents = file_get_contents($file);

                if (false === $contents) {
                    continue;
                }

                foreach ($this->extractor->extract($contents) as $message) {
                    $messages[$message] ??= $this->sourceUrl($file);
                }
            }
        }

        ksort($messages, \SORT_STRING);

        return $messages;
    }

    public function consolidate(
        string $culture,
        string $targetDirectory,
    ): int {
        $current = $this->readCulture($culture);
        $extracted = $this->extractMessages();
        $fixtures = $this->fixtureMessages($culture);
        $sources = array_unique([
            ...array_keys($current),
            ...array_keys($extracted),
            ...array_keys($fixtures),
        ]);
        sort($sources, \SORT_STRING);
        $messages = [];

        foreach ($sources as $source) {
            $messages[$source] = [
                'target' => $current[$source]['target']
                    ?? $fixtures[$source]['target']
                    ?? '',
                'id' => sha1($source),
                'note' => $extracted[$source]
                    ?? $current[$source]['note']
                    ?? $fixtures[$source]['note']
                    ?? '',
            ];

            if (
                '' === $messages[$source]['target']
                && '' !== ($fixtures[$source]['target'] ?? '')
            ) {
                $messages[$source]['target'] =
                    $fixtures[$source]['target'];
            }
        }

        $this->xliff->write(
            rtrim($targetDirectory, '/\\')
                .'/'.$culture.'/messages.xml',
            $culture,
            $messages,
        );

        return count($messages);
    }

    public function rectifyPlugins(string $culture): int
    {
        $current = $this->readCulture($culture);
        $changed = 0;

        foreach ($this->pluginTranslationDirectories() as $directory) {
            $path = $directory.'/'.$culture.'/messages.xml';

            if (!is_readable($path) || !is_writable($path)) {
                continue;
            }

            $messages = $this->xliff->read($path);
            $modified = false;

            foreach ($messages as $source => &$message) {
                $target = $current[$source]['target'] ?? '';

                if ('' !== $target && $message['target'] !== $target) {
                    $message['target'] = $target;
                    $modified = true;
                    ++$changed;
                }
            }
            unset($message);

            if ($modified) {
                $this->xliff->write($path, $culture, $messages);
            }
        }

        return $changed;
    }

    private function translationDirectories(): array
    {
        return [
            $this->projectDirectory
                .'/apps/'.$this->application.'/i18n',
            ...$this->pluginTranslationDirectories(),
        ];
    }

    private function pluginTranslationDirectories(): array
    {
        $directories = glob(
            $this->projectDirectory.'/plugins/*/i18n',
            \GLOB_ONLYDIR,
        ) ?: [];
        sort($directories, \SORT_STRING);

        return $directories;
    }

    private function sourceDirectories(): array
    {
        $applicationDirectory = $this->projectDirectory
            .'/apps/'.$this->application;
        $directories = [
            $applicationDirectory.'/templates',
            $applicationDirectory.'/lib',
            $applicationDirectory.'/modules',
            $this->projectDirectory.'/lib/form',
        ];

        foreach (glob($this->projectDirectory.'/plugins/*/modules') ?: [] as $modules) {
            $directories[] = $modules;
        }

        return array_values(array_filter($directories, 'is_dir'));
    }

    private function fixtureMessages(string $culture): array
    {
        $directories = [$this->projectDirectory.'/data/fixtures'];

        foreach (
            glob($this->projectDirectory.'/plugins/*/data/fixtures') ?: [] as $directory
        ) {
            $directories[] = $directory;
        }

        $messages = [];

        foreach (Finder::type('file')->name('*.yml')->in($directories) as $file) {
            $fixtures = Yaml::load($file);

            if (!is_array($fixtures)) {
                continue;
            }

            foreach ($fixtures as $class => $records) {
                if (!is_array($records)) {
                    continue;
                }

                foreach ($records as $record) {
                    $values = $this->translatedValues(
                        (string) $class,
                        $record,
                    );

                    if (!is_array($values) || !isset($values['en'])) {
                        continue;
                    }

                    $messages[$values['en']] = [
                        'target' => (string) ($values[$culture] ?? ''),
                        'id' => sha1((string) $values['en']),
                        'note' => $this->sourceUrl($file),
                    ];
                }
            }
        }

        return $messages;
    }

    private function translatedValues(
        string $class,
        mixed $record,
    ): ?array {
        if (!is_array($record)) {
            return null;
        }

        return match ($class) {
            'QubitAclGroup',
            'QubitTaxonomy',
            'QubitTerm' => $record['name'] ?? null,
            'QubitMenu' => $record['label'] ?? null,
            'QubitSetting' => in_array(
                $record['scope'] ?? null,
                \QubitSetting::$translatableScopes,
                true,
            ) ? ($record['value'] ?? null) : null,
            default => null,
        };
    }

    private function sourceUrl(string $path): string
    {
        $relative = ltrim(
            substr($path, strlen($this->projectDirectory)),
            '/\\',
        );

        return 'https://github.com/artefactual/atom/blob/master/'
            .str_replace('\\', '/', $relative);
    }
}
