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

namespace Atom\Framework\Bridge;

use Symfony\Component\Translation\Translator as SymfonyTranslator;

final readonly class TranslatorFactory
{
    private string $projectDirectory;

    public function __construct(
        string $projectDirectory,
        private string $application,
        private array $plugins,
    ) {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
    }

    public function create(string $culture): Translator
    {
        $translator = new SymfonyTranslator($culture);
        $translator->addLoader(
            'atom_xliff',
            new LegacyXliffFileLoader(),
        );

        foreach ($this->directories() as $directory) {
            $path = $directory.'/'.$culture.'/messages.xml';

            if (is_readable($path)) {
                $translator->addResource(
                    'atom_xliff',
                    $path,
                    $culture,
                    'messages',
                );
            }
        }

        return new Translator($translator);
    }

    private function directories(): array
    {
        $directories = [
            $this->projectDirectory
                .'/apps/'.$this->application.'/i18n',
        ];

        foreach ($this->plugins as $plugin) {
            $directories[] = $this->projectDirectory
                .'/plugins/'.$plugin.'/i18n';
        }

        return $directories;
    }
}
