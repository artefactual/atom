<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

require_once __DIR__.'/../../vendor/symfony/lib/yaml/sfYaml.php';

$configFile = __DIR__.'/../../config/appChallenge.yml';
// If the js challenge feature config file is missing, skip.
if (!is_readable($configFile)) {
    return;
}

$config = sfYaml::load($configFile);

// If the js challenge feature is disabled or user is already on the challenge page, skip.
$activated = (bool) ($config['activated'] ?? false);
if (!$activated || (0 === strpos($_SERVER['REQUEST_URI'], '/challenge'))) {
    return;
}

// Check if the request URI matches any of the endpoint exceptions.
$prefixes = $config['endpoint_exceptions'] ?? [];

$patterns = array_map(function (string $path) {
    // Escape any regex-special chars in the raw path.
    $escaped = preg_quote($path, '#');

    // Match exactly the path or any subpath.
    return '#^'.$escaped.'(/.*)?$#';
}, $prefixes);

$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestPath = parse_url($requestUri, PHP_URL_PATH) ?? '/';
foreach ($patterns as $pattern) {
    if (preg_match($pattern, $requestPath)) {
        return;
    }
}

// Load helper classes.
require_once __DIR__.'/../../lib/QubitGeoIpHelper.class.php';

require_once __DIR__.'/../../lib/QubitUserChallenge.class.php';

// Get Remote IP from request.
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;
if (!$remoteIp) {
    header('HTTP/1.1 500 Internal Server Error');

    exit;
}

// Compute cookie key.
$salt = $config['salt'] ?? '';
$key = md5($remoteIp.':'.$salt);

$visitedCookie = $config['cookiename_visited'] ?? 'atom_visited';
$headlessCookie = $config['cookiename_headless'] ?? 'atom_headless';
$jsCookie = $config['cookiename_js'] ?? 'atom_js';

$logger = null;
$geo = new QubitGeoIpHelper(
    $config['geoip_asn_db_path'],
    $config['geoip_city_db_path'],
    $logger
);
$userChallenge = new QubitUserChallenge(
    $config,
    $geo,
    $logger
);

// If user has 'visited' cookie, expire any challenge cookies.
if (
    isset($_COOKIE[$visitedCookie])
    && $_COOKIE[$visitedCookie] === $key
) {
    if (isset($_COOKIE[$headlessCookie])) {
        // expire headless cookie via helper
        $userChallenge->setCookie(
            $headlessCookie,
            '',
            time() - 3600,
            '/'
        );
    }
    if (isset($_COOKIE[$jsCookie]) && $_COOKIE[$jsCookie] === $key) {
        // expire js cookie via helper
        $userChallenge->setCookie(
            $jsCookie,
            '',
            time() - 3600,
            '/'
        );
    }

    return;
}

// GeoIP + User-Agent exceptions
$country = $geo->getCountry($remoteIp);
$asn = $geo->getAsn($remoteIp);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

// Allow user to bypass the challenge based on GeoIP, User-Agent etc.
if ($userChallenge->shouldBypassChallenge($remoteIp, $ua)) {
    return;
}

// If user passed the JS tests, grant visit and continue.
if (
    isset($_COOKIE[$jsCookie])
    && $_COOKIE[$jsCookie] === $key
    && $userChallenge->checkHeadless($key)
) {
    $expires = time() + 86400 * ($config['cookie_days'] ?? 90);
    // set 'visited' cookie via helper
    $userChallenge->setCookie(
        $visitedCookie,
        $key,
        $expires,
        '/'
    );

    return;
}

// Use JS localStorage to save the originally requested URI so
// user can be redirected later.
$nonce = base64_encode(random_bytes(16));
$rawUri = json_encode($_SERVER['REQUEST_URI']);
$noscriptMessage = htmlspecialchars(
  $config['noscript_message']
    ?? 'Please enable JavaScript in your browser and try again.',
  ENT_QUOTES
);

header(
  'Content-Security-Policy: default-src \'self\'; '.
  'script-src \'self\' \'nonce-'.$nonce.'\';'
);

echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Redirecting…</title>
</head>
<body>
  <script nonce="{$nonce}">
    (function(){
      // Store requested URL.
      try {
        localStorage.setItem('atom_challenge_redirect_url', {$rawUri});
      } catch (e) { /* ignore if unavailable */ }

      // Navigate to challenge page.
      window.location.href = '/challenge';
    })();
  </script>
  <noscript>
    <p><strong>Notice:</strong> {$noscriptMessage}</p>
  </noscript>
</body>
</html>
HTML;

exit;
