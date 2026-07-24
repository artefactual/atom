<?php

declare(strict_types=1);

use Atom\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/vendor/composer/autoload.php';

// Handle challenge URL requests immediately.
if (str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/challenge')) {
    chdir(__DIR__.'/web/challenge');

    require 'index.php';

    exit;
}

require __DIR__.'/lib/challenge/filter.php';

$kernel = new Kernel('prod', false);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
