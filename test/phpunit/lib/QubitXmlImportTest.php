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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitXmlImport
 */
class QubitXmlImportTest extends TestCase
{
    public function testReplacesLineBreaksWithTextNodesPresent()
    {
        $document = new DOMDocument();
        $document->loadXML(
            '<physdesc>Text <extent>1<lb/> item</extent> tail</physdesc>'
        );

        $value = QubitXmlImport::replaceLineBreaks(
            $document->documentElement
        );

        $this->assertStringContainsString("1\n item", $value);
        $this->assertStringContainsString('Text ', $value);
        $this->assertStringContainsString(' tail', $value);
    }

    public function testLoadXmlDoesNotSubstituteExternalEntities()
    {
        // Imported XML must not substitute external entity content into the DOM.
        $secretFile = tempnam(sys_get_temp_dir(), 'atom-xxe-secret-');
        $xmlFile = tempnam(sys_get_temp_dir(), 'atom-xxe-import-');

        file_put_contents($secretFile, 'ATOM-XXE-SHOULD-NOT-APPEAR');
        file_put_contents($xmlFile, sprintf(
            <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE ead [
  <!ENTITY xxe SYSTEM "file://%s">
]>
<ead>
  <archdesc>
    <did>
      <unittitle>&xxe;</unittitle>
    </did>
  </archdesc>
</ead>
XML,
            $secretFile
        ));

        try {
            $doc = $this->loadXmlWithImporter($xmlFile);

            $this->assertStringNotContainsString(
                'ATOM-XXE-SHOULD-NOT-APPEAR',
                $doc->saveXML(),
                'External entity contents must not be substituted into imported XML.'
            );
        } finally {
            @unlink($secretFile);
            @unlink($xmlFile);
        }
    }

    private function loadXmlWithImporter($xmlFile)
    {
        $importer = new QubitXmlImport();
        $method = new ReflectionMethod(QubitXmlImport::class, 'loadXML');
        $method->setAccessible(true);

        return $method->invoke($importer, $xmlFile, ['strictXmlParsing' => false]);
    }
}
