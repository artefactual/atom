<?php

declare(strict_types=1);

use Atom\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__.'/vendor/composer/autoload.php';

// This check prevents access to debug front controllers that are deployed by
// accident to production servers. Feel free to remove this, extend it or make
// something more sophisticated.

$allowedIps = ['127.0.0.1', '::1'];
if (false !== $envIp = getenv('ATOM_DEBUG_IP')) {
    $allowedIps = array_merge($allowedIps, array_filter(explode(',', $envIp)));
}

if (!in_array($_SERVER['REMOTE_ADDR'] ?? null, $allowedIps, true)) {
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

// Handle challenge URL requests immediately.
if (str_starts_with($_SERVER['REQUEST_URI'] ?? '/', '/challenge')) {
    chdir(__DIR__.'/web/challenge');

    require 'index.php';

    exit;
}

require __DIR__.'/lib/challenge/filter.php';

$kernel = new Kernel('dev', true);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
