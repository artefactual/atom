<?php

// This check prevents access to debug front controllers that are deployed by
// accident to production servers. Feel free to remove this, extend it or make
// something more sophisticated.

$allowedIps = ['127.0.0.1', '::1'];
if (false !== $envIp = getenv('ATOM_DEBUG_IP')) {
    $allowedIps = array_merge($allowedIps, array_filter(explode(',', $envIp)));
}

if (!in_array(@$_SERVER['REMOTE_ADDR'], $allowedIps)) {
    exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
}

// Handle challenge URL requests immediately.
if (in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ['/challenge', '/challenge/', '/challenge/proof', '/challenge/verify', '/challenge/report'], true)) {
    chdir(__DIR__.'/web/challenge');

    require 'index.php';

    exit;
}

require __DIR__.'/lib/challenge/filter.php';

require_once dirname(__FILE__).'/config/ProjectConfiguration.class.php';

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'dev', true);
sfContext::createInstance($configuration)->dispatch();
