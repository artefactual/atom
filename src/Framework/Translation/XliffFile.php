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

final readonly class XliffFile
{
    public function read(string $path): array
    {
        $document = $this->load($path);

        $messages = [];

        foreach ($document->getElementsByTagName('trans-unit') as $unit) {
            $source = $this->childText($unit, 'source');

            if ('' === $source) {
                continue;
            }

            $messages[$source] = [
                'target' => $this->childText($unit, 'target'),
                'id' => $unit->getAttribute('id'),
                'note' => $this->childText($unit, 'note'),
            ];
        }

        return $messages;
    }

    public function updateTarget(
        string $path,
        string $source,
        string $target,
    ): bool {
        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf(
                'XLIFF file "%s" is not writable.',
                $path,
            ));
        }

        $document = $this->load($path);

        foreach ($document->getElementsByTagName('trans-unit') as $unit) {
            if ($source !== $this->childText($unit, 'source')) {
                continue;
            }

            $targetNode = $this->childElement($unit, 'target');

            if (null === $targetNode) {
                $targetNode = $document->createElement('target');
                $note = $this->childElement($unit, 'note');
                $unit->insertBefore($targetNode, $note);
            }

            while (null !== $targetNode->firstChild) {
                $targetNode->removeChild($targetNode->firstChild);
            }

            if ('' === $target) {
                $targetNode->removeAttribute('state');
            } else {
                $targetNode->setAttribute('state', 'final');
                $targetNode->appendChild(
                    $document->createTextNode($target),
                );
            }

            $file = $document->getElementsByTagName('file')->item(0);

            if ($file instanceof \DOMElement) {
                $file->setAttribute('date', gmdate('Y-m-d\TH:i:s\Z'));
            }

            $this->save($document, $path);

            return true;
        }

        return false;
    }

    public function removeDuplicateSources(string $path): int
    {
        if (!is_writable($path)) {
            throw new \RuntimeException(sprintf(
                'XLIFF file "%s" is not writable.',
                $path,
            ));
        }

        $document = $this->load($path);
        $sources = [];
        $removed = 0;

        foreach ($document->getElementsByTagName('trans-unit') as $unit) {
            $sourceNode = $this->childElement($unit, 'source');

            if (null === $sourceNode) {
                continue;
            }

            $source = $sourceNode->textContent;
            $target = $this->childElement($unit, 'target');

            if (!array_key_exists($source, $sources)) {
                $sources[$source] = [$unit, $target];

                continue;
            }

            [$originalUnit, $originalTarget] = $sources[$source];

            if (
                $target instanceof \DOMElement
                && '' !== $target->textContent
                && (
                    !$originalTarget instanceof \DOMElement
                    || '' === $originalTarget->textContent
                )
            ) {
                if (!$originalTarget instanceof \DOMElement) {
                    $originalTarget = $document->createElement('target');
                    $note = $this->childElement($originalUnit, 'note');
                    $originalUnit->insertBefore($originalTarget, $note);
                    $sources[$source][1] = $originalTarget;
                }

                $originalTarget->setAttribute('state', 'final');
                $originalTarget->nodeValue = $target->textContent;
            }

            $unit->parentNode?->removeChild($unit);
            ++$removed;
        }

        if (0 < $removed) {
            $file = $document->getElementsByTagName('file')->item(0);

            if ($file instanceof \DOMElement) {
                $file->setAttribute('date', gmdate('Y-m-d\TH:i:s\Z'));
            }

            $this->save($document, $path);
        }

        return $removed;
    }

    public function write(
        string $path,
        string $culture,
        array $messages,
    ): void {
        $directory = dirname($path);

        if (
            !is_dir($directory)
            && !mkdir($directory, 0777, true)
            && !is_dir($directory)
        ) {
            throw new \RuntimeException(sprintf(
                'Unable to create XLIFF directory "%s".',
                $directory,
            ));
        }

        $implementation = new \DOMImplementation();
        $documentType = $implementation->createDocumentType(
            'xliff',
            '-//XLIFF//DTD XLIFF//EN',
            'http://www.oasis-open.org/committees/xliff/documents/xliff.dtd',
        );
        $document = $implementation->createDocument('', 'xliff', $documentType);
        $document->encoding = 'UTF-8';
        $document->formatOutput = true;
        $document->preserveWhiteSpace = false;
        $root = $document->documentElement;
        $root->setAttribute('version', '1.0');

        $file = $document->createElement('file');
        $file->setAttribute('source-language', 'en');
        $file->setAttribute('target-language', $culture);
        $file->setAttribute('datatype', 'plaintext');
        $file->setAttribute('original', 'messages');
        $file->setAttribute('date', gmdate('Y-m-d\TH:i:s\Z'));
        $file->setAttribute('product-name', 'messages');
        $root->appendChild($file);
        $file->appendChild($document->createElement('header'));
        $body = $document->createElement('body');
        $file->appendChild($body);

        ksort($messages, \SORT_STRING);

        foreach ($messages as $source => $message) {
            $source = (string) $source;
            $target = (string) ($message['target'] ?? '');
            $unit = $document->createElement('trans-unit');
            $unit->setAttribute(
                'id',
                (string) ($message['id'] ?? '') ?: sha1($source),
            );
            $sourceNode = $document->createElement('source');
            $sourceNode->setAttribute('xml:space', 'preserve');
            $sourceNode->appendChild($document->createTextNode($source));
            $unit->appendChild($sourceNode);
            $targetNode = $document->createElement('target');
            $targetNode->setAttribute('xml:space', 'preserve');

            if ('' !== $target) {
                $targetNode->setAttribute('state', 'final');
                $targetNode->appendChild(
                    $document->createTextNode($target),
                );
            }

            $unit->appendChild($targetNode);
            $note = (string) ($message['note'] ?? '');

            if ('' !== $note) {
                $noteNode = $document->createElement('note');
                $noteNode->appendChild($document->createTextNode($note));
                $unit->appendChild($noteNode);
            }

            $body->appendChild($unit);
        }

        $this->save($document, $path);
    }

    private function load(string $path): \DOMDocument
    {
        if (!is_readable($path)) {
            throw new \RuntimeException(sprintf(
                'XLIFF file "%s" is not readable.',
                $path,
            ));
        }

        $document = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            $loaded = $document->load(
                $path,
                \LIBXML_NONET | \LIBXML_NOCDATA,
            );
            $errors = libxml_get_errors();
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if ($loaded) {
            return $document;
        }

        $messages = array_map(
            static fn (\LibXMLError $error): string => trim(
                $error->message,
            ),
            $errors,
        );

        throw new \RuntimeException(sprintf(
            'Unable to parse XLIFF file "%s": %s',
            $path,
            implode('; ', $messages),
        ));
    }

    private function save(\DOMDocument $document, string $path): void
    {
        $directory = dirname($path);
        $mode = file_exists($path)
            ? fileperms($path) & 0777
            : 0666 & ~umask();
        $temporary = tempnam($directory, '.messages-');

        if (
            false === $temporary
            || false === $document->save($temporary)
            || !chmod($temporary, $mode)
            || !rename($temporary, $path)
        ) {
            if (is_string($temporary) && file_exists($temporary)) {
                unlink($temporary);
            }

            throw new \RuntimeException(sprintf(
                'Unable to write XLIFF file "%s".',
                $path,
            ));
        }
    }

    private function childText(
        \DOMElement $unit,
        string $name,
    ): string {
        foreach ($unit->childNodes as $node) {
            if ($node instanceof \DOMElement && $name === $node->localName) {
                return $node->textContent;
            }
        }

        return '';
    }

    private function childElement(
        \DOMElement $unit,
        string $name,
    ): ?\DOMElement {
        foreach ($unit->childNodes as $node) {
            if ($node instanceof \DOMElement && $name === $node->localName) {
                return $node;
            }
        }

        return null;
    }
}
