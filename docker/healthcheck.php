#!/usr/bin/env php
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

use Atom\Framework\Health\Probe;

require dirname(__DIR__).'/vendor/composer/autoload.php';

$mode = $argv[1] ?? 'ready';

try {
    $result = Probe::fromGlobals()->run($mode);
} catch (Throwable $exception) {
    $result = [
        'status' => 'fail',
        'mode' => $mode,
        'checks' => [
            'probe' => [
                'status' => 'fail',
                'message' => $exception->getMessage(),
            ],
        ],
    ];
}

fwrite(
    STDOUT,
    json_encode(
        $result,
        JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES,
    ).PHP_EOL,
);

exit('ok' === $result['status'] ? 0 : 1);
