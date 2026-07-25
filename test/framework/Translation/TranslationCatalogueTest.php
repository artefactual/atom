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

namespace Atom\Tests\Framework\Translation;

use Atom\Framework\Translation\TranslationCatalogue;
use Atom\Framework\Translation\XliffFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class TranslationCatalogueTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-catalogue-'.bin2hex(
            random_bytes(8),
        );
        mkdir($this->directory.'/apps/qubit/i18n/fr', 0777, true);
        mkdir($this->directory.'/apps/qubit/modules/example/templates', 0777, true);
        mkdir($this->directory.'/plugins/examplePlugin/i18n/fr', 0777, true);
        mkdir($this->directory.'/data/fixtures', 0777, true);
        mkdir($this->directory.'/output', 0777, true);
        $xliff = new XliffFile();
        $xliff->write(
            $this->directory.'/apps/qubit/i18n/fr/messages.xml',
            'fr',
            [
                'Current message' => [
                    'target' => 'Message actuel',
                    'note' => 'application',
                ],
            ],
        );
        $xliff->write(
            $this->directory
                .'/plugins/examplePlugin/i18n/fr/messages.xml',
            'fr',
            [
                'Plugin message' => [
                    'target' => 'Message du module',
                    'note' => 'plugin',
                ],
            ],
        );
        file_put_contents(
            $this->directory
                .'/apps/qubit/modules/example/templates/index.php',
            "<?php echo __('Extracted message');\n",
        );
        file_put_contents(
            $this->directory.'/data/fixtures/terms.yml',
            <<<'YAML'
                QubitTerm:
                  fixture_term:
                    name:
                      en: Fixture message
                      fr: Message de fixture
                YAML,
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

    public function testConsolidatesCodeCataloguesAndFixtures(): void
    {
        $catalogue = new TranslationCatalogue($this->directory);

        self::assertSame(
            4,
            $catalogue->consolidate('fr', $this->directory.'/output'),
        );

        $messages = (new XliffFile())->read(
            $this->directory.'/output/fr/messages.xml',
        );
        self::assertSame(
            'Message actuel',
            $messages['Current message']['target'],
        );
        self::assertSame(
            'Message du module',
            $messages['Plugin message']['target'],
        );
        self::assertSame(
            'Message de fixture',
            $messages['Fixture message']['target'],
        );
        self::assertSame('', $messages['Extracted message']['target']);
        self::assertStringEndsWith(
            'apps/qubit/modules/example/templates/index.php',
            $messages['Extracted message']['note'],
        );
    }
}
