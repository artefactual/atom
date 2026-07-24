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

use Symfony\Component\Translation\Exception\InvalidResourceException;
use Symfony\Component\Translation\Loader\LoaderInterface;
use Symfony\Component\Translation\MessageCatalogue;

final readonly class LegacyXliffFileLoader implements LoaderInterface
{
    public function load(
        mixed $resource,
        string $locale,
        string $domain = 'messages',
    ): MessageCatalogue {
        if (!is_string($resource) || !is_readable($resource)) {
            throw new InvalidResourceException(sprintf(
                'Translation resource "%s" is not readable.',
                (string) $resource,
            ));
        }

        $contents = file_get_contents($resource);

        if (false === $contents) {
            throw new InvalidResourceException(sprintf(
                'Translation resource "%s" could not be read.',
                $resource,
            ));
        }

        $contents = preg_replace(
            '/<!DOCTYPE[^>]*>/i',
            '',
            $contents,
        ) ?? $contents;
        $previous = libxml_use_internal_errors(true);

        try {
            $xml = simplexml_load_string(
                $contents,
                \SimpleXMLElement::class,
                \LIBXML_NONET | \LIBXML_NOCDATA,
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        if (false === $xml) {
            throw new InvalidResourceException(sprintf(
                'Translation resource "%s" is not valid XLIFF.',
                $resource,
            ));
        }

        $catalogue = new MessageCatalogue($locale);

        foreach ($xml->file->body->{'trans-unit'} as $unit) {
            $source = (string) $unit->source;
            $target = (string) $unit->target;

            if ('' !== $source && '' !== $target) {
                $catalogue->set($source, $target, $domain);
            }
        }

        return $catalogue;
    }
}
