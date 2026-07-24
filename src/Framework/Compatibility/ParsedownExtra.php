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

namespace Atom\Framework\Compatibility;

final class ParsedownExtra extends \ParsedownExtra
{
    protected function processTag($elementMarkup)
    {
        libxml_use_internal_errors(true);
        $document = new \DOMDocument();
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
                    $node instanceof \DOMElement
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
