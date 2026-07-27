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

namespace Atom\Tests\Framework\Translation;

use Atom\Framework\Translation\TranslationStore;
use Atom\Framework\Translation\XliffFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TranslationStoreTest extends TestCase
{
    private string $directory;
    private string $application;
    private string $plugin;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-store-'.bin2hex(
            random_bytes(8),
        );
        $this->application = $this->directory.'/application';
        $this->plugin = $this->directory.'/plugin';
        mkdir($this->application.'/fr', 0777, true);
        mkdir($this->plugin.'/fr', 0777, true);
        $xliff = new XliffFile();
        $xliff->write(
            $this->application.'/fr/messages.xml',
            'fr',
            [
                'Shared source' => [
                    'target' => 'Application',
                    'id' => 'application-id',
                ],
            ],
        );
        $xliff->write(
            $this->plugin.'/fr/messages.xml',
            'fr',
            [
                'Shared source' => [
                    'target' => 'Plugin',
                    'id' => 'plugin-id',
                ],
            ],
        );
    }

    protected function tearDown(): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $this->directory,
                \FilesystemIterator::SKIP_DOTS,
            ),
            \RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($iterator as $item) {
            $item->isDir()
                ? rmdir($item->getPathname())
                : unlink($item->getPathname());
        }

        rmdir($this->directory);
    }

    public function testFindsAndUpdatesTheFirstCatalogue(): void
    {
        $store = new TranslationStore(
            [$this->application, $this->plugin],
            'fr',
        );

        self::assertSame(
            'Application',
            $store->find('Shared source')['target'],
        );
        self::assertTrue($store->update(
            'Shared source',
            'Updated application',
        ));
        self::assertFalse($store->update('Missing source', 'Missing'));

        $xliff = new XliffFile();
        self::assertSame(
            'Updated application',
            $xliff->read(
                $this->application.'/fr/messages.xml',
            )['Shared source']['target'],
        );
        self::assertSame(
            'Plugin',
            $xliff->read(
                $this->plugin.'/fr/messages.xml',
            )['Shared source']['target'],
        );
    }

    public function testReusesDiscoveredCataloguePaths(): void
    {
        $store = new TranslationStore(
            [$this->application, $this->plugin],
            'fr',
        );

        self::assertSame(
            'Application',
            $store->find('Shared source')['target'],
        );

        unlink($this->application.'/fr/messages.xml');

        self::assertSame(
            'Application',
            $store->find('Shared source')['target'],
        );
    }
}
