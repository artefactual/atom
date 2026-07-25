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

use Atom\Framework\Translation\XliffFile;
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

        try {
            $messages = (new XliffFile())->read($resource);
        } catch (\RuntimeException $exception) {
            throw new InvalidResourceException(
                $exception->getMessage(),
                0,
                $exception,
            );
        }

        $catalogue = new MessageCatalogue($locale);

        foreach ($messages as $source => $message) {
            $target = $message['target'];

            if ('' !== $source) {
                $catalogue->set(
                    $source,
                    '' === $target ? $source : $target,
                    $domain,
                );
            }
        }

        return $catalogue;
    }
}
