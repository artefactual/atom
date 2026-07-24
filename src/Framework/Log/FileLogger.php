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

namespace Atom\Framework\Log;

use Atom\Framework\Bridge\EventDispatcher;

final class FileLogger extends Logger
{
    private mixed $stream = null;

    public function initialize(
        EventDispatcher $dispatcher,
        $options = [],
    ) {
        $path = $options['file'] ?? null;

        if (!is_string($path) || '' === $path) {
            throw new \InvalidArgumentException(
                'A log file path is required.',
            );
        }

        $directory = dirname($path);

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $this->stream = fopen($path, 'ab');

        if (false === $this->stream) {
            throw new \RuntimeException(sprintf(
                'Unable to open log file "%s".',
                $path,
            ));
        }

        parent::initialize($dispatcher, $options);
    }

    public function shutdown(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    protected function doLog($message, $priority)
    {
        fwrite($this->stream, sprintf(
            "%s [%-7s] %s\n",
            date('M d H:i:s'),
            self::getPriorityName($priority),
            $message,
        ));
    }
}
