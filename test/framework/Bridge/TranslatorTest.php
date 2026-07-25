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

namespace Atom\Tests\Framework\Bridge;

use Atom\Framework\Bridge\RequestAdapter;
use Atom\Framework\Bridge\TranslatorFactory;
use Atom\Framework\Translation\XliffFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @internal
 */
final class TranslatorTest extends TestCase
{
    public function testLoadsLegacyAtoMXliffCatalogue(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $translator = (new TranslatorFactory(
            $projectDirectory,
            'qubit',
            ['qbAclPlugin'],
        ))->create('fr');

        self::assertSame('Accueil', $translator->__('Home'));
        self::assertSame('Fermer', $translator->__('Close'));
        self::assertSame(
            'Imprimé : 2026-07-24',
            $translator->__('Printed: %d%', [
                '%d%' => '2026-07-24',
            ]),
        );
        self::assertSame(
            'Skip to main content',
            $translator->__('Skip to main content'),
        );
    }

    public function testFallsBackToSourceInDefaultCulture(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $translator = (new TranslatorFactory(
            $projectDirectory,
            'qubit',
            [],
        ))->create('en');

        self::assertSame('Home', $translator->__('Home'));
    }

    public function testTracksAndUpdatesTheFirstLegacyCatalogue(): void
    {
        $directory = sys_get_temp_dir().'/atom-translator-'.bin2hex(
            random_bytes(8),
        );
        $application = $directory.'/apps/qubit/i18n/fr';
        $plugin = $directory.'/plugins/examplePlugin/i18n/fr';
        mkdir($application, 0777, true);
        mkdir($plugin, 0777, true);
        $xliff = new XliffFile();
        $xliff->write($application.'/messages.xml', 'fr', [
            'Hello %name%' => [
                'target' => 'Bonjour %name%',
                'id' => 'application',
            ],
        ]);
        $xliff->write($plugin.'/messages.xml', 'fr', [
            'Hello %name%' => [
                'target' => 'Salut %name%',
                'id' => 'plugin',
            ],
        ]);

        try {
            $request = new RequestAdapter(new Request());
            $factory = new TranslatorFactory(
                $directory,
                'qubit',
                ['examplePlugin'],
            );
            $translator = $factory->create('fr', $request);

            self::assertSame(
                'Bonjour Alice',
                $translator->__('Hello %name%', [
                    '%name%' => 'Alice',
                ]),
            );
            self::assertSame([
                'Hello %name%' => 'Bonjour %name%',
            ], $request->getAttribute('messages'));
            self::assertTrue($translator->update(
                'Hello %name%',
                'Bienvenue %name%',
            ));
            self::assertSame(
                'Bienvenue Alice',
                $factory->create('fr')->__('Hello %name%', [
                    '%name%' => 'Alice',
                ]),
            );
        } finally {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $directory,
                    \FilesystemIterator::SKIP_DOTS,
                ),
                \RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($iterator as $item) {
                $item->isDir()
                    ? rmdir($item->getPathname())
                    : unlink($item->getPathname());
            }

            rmdir($directory);
        }
    }
}
