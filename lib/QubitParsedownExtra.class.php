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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General
 * Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

class QubitParsedownExtra extends ParsedownExtra
{
    protected function inlineLink($excerpt)
    {
        $element = [
            'name' => 'a',
            'handler' => 'line',
            'nonNestables' => ['Url', 'Link'],
            'text' => null,
            'attributes' => [
                'href' => null,
                'title' => null,
            ],
        ];
        $extent = 0;
        $remainder = $excerpt['text'];

        if (!preg_match(
            '/\[((?:[^][]++|(?R))*+)\]/',
            $remainder,
            $matches,
        )) {
            return;
        }

        $element['text'] = $matches[1];
        $extent += strlen($matches[0]);
        $remainder = substr($remainder, $extent);

        if (preg_match(
            '/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)'
                .'(?:[ ]+("[^"]*"|\'[^\']*\'))?\s*[)]/',
            $remainder,
            $matches,
        )) {
            $element['attributes']['href'] = $matches[1];

            if (isset($matches[2])) {
                $element['attributes']['title'] = substr(
                    $matches[2],
                    1,
                    -1,
                );
            }

            $extent += strlen($matches[0]);
        } else {
            if (preg_match(
                '/^\s*\[(.*?)\]/',
                $remainder,
                $matches,
            )) {
                $definition = strlen($matches[1])
                    ? $matches[1]
                    : $element['text'];
                $extent += strlen($matches[0]);
            } else {
                $definition = $element['text'];
            }

            $definition = strtolower($definition);

            if (!isset($this->DefinitionData['Reference'][$definition])) {
                return;
            }

            $reference = $this->DefinitionData['Reference'][$definition];
            $element['attributes']['href'] = $reference['url'];
            $element['attributes']['title'] = $reference['title'];
        }

        $link = [
            'extent' => $extent,
            'element' => $element,
        ];
        $remainder = substr($excerpt['text'], $link['extent']);

        if (preg_match(
            '/^[ ]*{('.$this->regexAttribute.'+)}/',
            $remainder,
            $matches,
        )) {
            $link['element']['attributes'] += $this->parseAttributeData(
                $matches[1],
            );
            $link['extent'] += strlen($matches[0]);
        }

        return $link;
    }

    protected function processTag($elementMarkup)
    {
        libxml_use_internal_errors(true);
        $document = new DOMDocument();
        $elementMarkup = mb_encode_numericentity(
            $elementMarkup,
            [0x80, 0x10FFFF, 0, 0xFFFFFF],
            'UTF-8',
        );
        $document->loadHTML($elementMarkup);
        $document->removeChild($document->doctype);
        $document->replaceChild(
            $document->firstChild->firstChild->firstChild,
            $document->firstChild,
        );
        $elementText = '';

        if (
            '1'
            === $document->documentElement->getAttribute('markdown')
        ) {
            foreach (
                $document->documentElement->childNodes as $node
            ) {
                $elementText .= $document->saveHTML($node);
            }

            $document->documentElement->removeAttribute('markdown');
            $elementText = "\n".$this->text($elementText)."\n";
        } else {
            foreach (
                $document->documentElement->childNodes as $node
            ) {
                $nodeMarkup = $document->saveHTML($node);

                if (
                    $node instanceof DOMElement
                    && !in_array(
                        $node->nodeName,
                        $this->textLevelElements,
                    )
                ) {
                    $elementText .= $this->processTag($nodeMarkup);
                } else {
                    $elementText .= $nodeMarkup;
                }
            }
        }

        $document->documentElement->nodeValue = 'placeholder\x1A';
        $markup = $document->saveHTML(
            $document->documentElement,
        );

        return str_replace(
            'placeholder\x1A',
            $elementText,
            $markup,
        );
    }
}
