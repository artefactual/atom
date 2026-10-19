<?php

// Handle challenge URL requests immediately.
if (in_array(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), ['/challenge', '/challenge/', '/challenge/proof', '/challenge/verify', '/challenge/report'], true)) {
    chdir(__DIR__.'/web/challenge');

    require 'index.php';

    exit;
}

require __DIR__.'/lib/challenge/filter.php';

require_once dirname(__FILE__).'/config/ProjectConfiguration.class.php';

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'prod', false);
sfContext::createInstance($configuration)->dispatch();
