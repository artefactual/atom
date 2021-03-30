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
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Remove HTML tags from various i18n table fields.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class i18nRemoveHtmlTagsTask extends i18nTransformBaseTask
{
    /**
     * @see sfTask
     */
    protected function configure()
    {
        parent::configure();

        $this->namespace = 'i18n';
        $this->name = 'remove-html-tags';
        $this->briefDescription = 'Remove HTML tags from inside various i18n fields, and convert HTML entities';

        $this->detailedDescription = <<<'EOF'
Remove HTML tags from inside information object, actor, note, repository, and rights i18n fields.
HTML character entities are also converted to their non-HTML representations.
EOF;
    }

    /**
     * @see i18nProcessColumnsBaseTask
     *
     * @param mixed $row
     * @param mixed $tableName
     * @param mixed $columns
     */
    protected function processRow($row, $tableName, $columns)
    {
        // Determine what column values contain HTML
        $columnValues = [];

        foreach ($columns as $column) {
            // Store column name/value for processing if it contains tags
            if ($row[$column] && (($row[$column] != strip_tags($row[$column])) || ($row[$column] != html_entity_decode($row[$column])))) {
                $columnValues[$column] = $this->transformHtmlToText($row[$column]);
            }
        }

        // Update database with transformed column values
        $this->updateRow($tableName, $row['id'], $row['culture'], $columnValues);

        return count($columnValues);
    }

    /**
     * Transform HTML into text using the Document Object Model.
     *
     * @param string $html HTML to transform into text
     *
     * @return string transformed text
     */
    private function transformHtmlToText($html)
    {
        // Parse HTML
        $doc = new DOMDocument();
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        // Apply transformations
        $this->transformDocument($doc);

        // Convert to string and strip leading/trailing whitespace
        return trim(htmlspecialchars_decode(strip_tags($doc->saveXml($doc->documentElement))));
    }

    /**
     * Transform specific tags within a DOM document.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocument(&$doc)
    {
        // Create text representations of various HTML tags
        $this->transformDocumentLinks($doc);
        $this->transformDocumentLists($doc);
        $this->transformDocumentDescriptionLists($doc);
        $this->transformDocumentBreaks($doc);

        // Deal with paragraphs last, as other transformations create them
        $this->transformDocumentParasIntoNewlines($doc);
    }

    /**
     * Transform link tags into text.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocumentLinks(&$doc)
    {
        $linkList = $doc->getElementsByTagName('a');

        // Loop through each <a> tag and replace with text content
        while ($linkList->length > 0) {
            $linkNode = $linkList->item(0);

            $linkText = $linkNode->textContent;
            $linkHref = $linkNode->getAttribute('href');

            if ($linkHref) {
                // Convert <a href="url">label</a> link to Markdown style [label](url) link.
                $linkText = sprintf('[%s](%s)', $linkText, $linkHref);
            }

            $newTextNode = $doc->createTextNode($linkText);
            $linkNode->parentNode->replaceChild($newTextNode, $linkNode);
        }
    }

    /**
     * Transform unordered list-related tags into text and enclose in
     * <p> tags.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocumentLists(&$doc)
    {
        $ulList = $doc->getElementsByTagName('ul');

        // Loop through each <ul> tag and change to a <p> tag
        while ($ulList->length > 0) {
            $listNode = $ulList->item(0);

            $newParaNode = $doc->createElement('p');

            // Assemble text representation of list
            $paraText = '';

            foreach ($listNode->childNodes as $childNode) {
                $paraText .= '* '.$childNode->textContent."\n";
            }

            // Set <p> element's text
            $newTextNode = $doc->createTextNode($paraText);
            $newParaNode->appendChild($newTextNode);

            $listNode->parentNode->replaceChild($newParaNode, $listNode);
        }
    }

    /**
     * Transform description list-related tags.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocumentDescriptionLists(&$doc)
    {
        $termList = $doc->getElementsByTagName('dt');

        // Loop through each <dt> tag and remove it
        while ($termList->length > 0) {
            $termNode = $termList->item(0);
            $termNode->parentNode->removeChild($termNode);
        }

        $descriptionList = $doc->getElementsByTagName('dd');

        // Look through each <dd> element and change to a <p> element
        while ($descriptionList->length > 0) {
            $descriptionNode = $descriptionList->item(0);
            // Create <p> node with description's text
            $newParaNode = $doc->createElement('p');
            $newTextNode = $doc->createTextNode($descriptionNode->textContent);
            $newParaNode->appendChild($newTextNode);
            $descriptionNode->parentNode->replaceChild($newParaNode, $descriptionNode);
        }
    }

    /**
     * Transform break tags into newlines.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocumentBreaks(&$doc)
    {
        $breakList = $doc->getElementsByTagName('br');

        // Loop through each <p> and replace with text
        while ($breakList->length) {
            $breakNode = $breakList->item(0);

            $newTextNode = $doc->createTextNode("\n");

            $breakNode->parentNode->replaceChild($newTextNode, $breakNode);
        }
    }

    /**
     * Transform paragraph tags into newlines.
     *
     * @param DOMDocument $doc DOM document
     */
    private function transformDocumentParasIntoNewlines(&$doc)
    {
        $paraList = $doc->getElementsByTagName('p');

        // Loop through each <p> and replace with text
        while ($paraList->length) {
            $paraNode = $paraList->item(0);

            $paraText = "\n".$paraNode->textContent."\n";
            $newTextNode = $doc->createTextNode($paraText);

            $paraNode->parentNode->replaceChild($newTextNode, $paraNode);
        }
    }
}
