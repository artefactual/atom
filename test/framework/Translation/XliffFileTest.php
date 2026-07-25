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

use Atom\Framework\Translation\XliffFile;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class XliffFileTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-xliff-'.bin2hex(
            random_bytes(8),
        );
        mkdir($this->directory, 0777, true);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }

        rmdir($this->directory);
    }

    public function testWritesAndReadsLegacyXliff(): void
    {
        $path = $this->directory.'/messages.xml';
        $messages = [
            'Source & <value>' => [
                'target' => 'Cible & <valeur>',
                'id' => 'existing-id',
                'note' => 'A source file',
            ],
            'Untranslated' => [
                'target' => '',
                'id' => '',
                'note' => '',
            ],
        ];
        $xliff = new XliffFile();
        $xliff->write($path, 'fr', $messages);

        self::assertSame([
            'Source & <value>' => [
                'target' => 'Cible & <valeur>',
                'id' => 'existing-id',
                'note' => 'A source file',
            ],
            'Untranslated' => [
                'target' => '',
                'id' => sha1('Untranslated'),
                'note' => '',
            ],
        ], $xliff->read($path));

        $document = new \DOMDocument();
        self::assertTrue($document->load($path, \LIBXML_NONET));
        self::assertSame(
            'fr',
            $document->getElementsByTagName('file')
                ->item(0)
                ?->getAttribute('target-language'),
        );

        $mode = fileperms($path) & 0777;

        self::assertTrue($xliff->updateTarget(
            $path,
            'Source & <value>',
            'Nouvelle cible',
        ));
        self::assertSame($mode, fileperms($path) & 0777);
        self::assertSame([
            'target' => 'Nouvelle cible',
            'id' => 'existing-id',
            'note' => 'A source file',
        ], $xliff->read($path)['Source & <value>']);
        self::assertFalse($xliff->updateTarget(
            $path,
            'Missing source',
            'Cible',
        ));
    }

    public function testRemovesDuplicatesWithoutLosingATranslation(): void
    {
        $path = $this->directory.'/duplicates.xml';
        file_put_contents($path, <<<'XML'
            <?xml version="1.0" encoding="UTF-8"?>
            <xliff version="1.0">
              <file source-language="en" target-language="fr">
                <body>
                  <trans-unit id="first">
                    <source>Duplicate</source>
                    <target></target>
                  </trans-unit>
                  <trans-unit id="second">
                    <source>Duplicate</source>
                    <target>Traduction</target>
                  </trans-unit>
                </body>
              </file>
            </xliff>
            XML);
        $xliff = new XliffFile();

        self::assertSame(1, $xliff->removeDuplicateSources($path));
        self::assertSame(
            'Traduction',
            $xliff->read($path)['Duplicate']['target'],
        );
        self::assertSame(0, $xliff->removeDuplicateSources($path));
    }
}
