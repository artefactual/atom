<?php

// The challenge runs before Symfony. Its tests need Composer and the two
// helpers, without configuring an AtoM database, search index or session cache.
$root = dirname(__DIR__, 2);

require_once $root.'/vendor/composer/autoload.php';

require_once $root.'/lib/QubitGeoIpHelper.class.php';

require_once $root.'/lib/QubitUserChallenge.class.php';
