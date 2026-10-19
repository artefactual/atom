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

require_once __DIR__.'/../QubitChallengeConfig.class.php';

$configFile = __DIR__.'/../../config/appChallenge.yml';
$config = QubitChallengeConfig::load($configFile, __DIR__.'/../../cache/challenge');
// If the js challenge feature config file is missing, skip.
if (null === $config) {
    return;
}

// If the challenge feature is disabled, skip.
$activated = (bool) ($config['activated'] ?? false);
if (!$activated) {
    return;
}

require_once __DIR__.'/../QubitChallengeDecisionLog.class.php';

// Load helper class.
require_once __DIR__.'/../../lib/QubitUserChallenge.class.php';

// Check if the request URI matches any of the endpoint exceptions.
$prefixes = $config['endpoint_exceptions'] ?? [];
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
if (QubitUserChallenge::matchesEndpointException($requestUri, $prefixes)) {
    QubitChallengeDecisionLog::write($config, 'skipped', 'endpoint_exception');

    return;
}

// Get Remote IP from request.
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;
if (!$remoteIp) {
    QubitChallengeDecisionLog::write($config, 'error', 'missing_client_ip');
    header('HTTP/1.1 500 Internal Server Error');

    exit;
}

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$useAltcha = 'altcha' === ($config['verification'] ?? 'legacy');
$proof = null;
if ($useAltcha) {
    require_once __DIR__.'/../QubitChallengeProof.class.php';

    try {
        $proof = new QubitChallengeProof($config, __DIR__.'/../../cache/challenge/replay');
    } catch (RuntimeException $e) {
        QubitChallengeDecisionLog::write($config, 'error', 'verification_unavailable');
        http_response_code(503);
        header('Cache-Control: no-store');
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Browser verification is temporarily unavailable. Please try again later.';

        exit;
    }
    // Validate server-issued clearance before constructing any GeoIP helpers.
    if ($proof->validClearance($_COOKIE[$config['cookiename_visited'] ?? 'atom_visited'] ?? null, $remoteIp, $ua)) {
        QubitChallengeDecisionLog::write($config, 'visited', 'valid_clearance');

        return;
    }
}

// Compute legacy cookie key (never accepted in ALTCHA mode).
$salt = $config['salt'] ?? '';
$key = md5($remoteIp.':'.$salt);

$visitedCookie = $config['cookiename_visited'] ?? 'atom_visited';
$headlessCookie = $config['cookiename_headless'] ?? 'atom_headless';
$jsCookie = $config['cookiename_js'] ?? 'atom_js';

require_once __DIR__.'/../../lib/QubitGeoIpHelper.class.php';

$logger = null;
$proxyGeoIp = null;
$geoIpFromNginx = $config['geoip_from_nginx'] ?? 'auto';
if (true === $geoIpFromNginx
    || ('auto' === $geoIpFromNginx
        && (array_key_exists('GEOIP_ASN', $_SERVER) || array_key_exists('GEOIP_COUNTRY', $_SERVER)))) {
    $proxyGeoIp = [
        'asn' => $_SERVER['GEOIP_ASN'] ?? '',
        'country' => $_SERVER['GEOIP_COUNTRY'] ?? '',
    ];
}
$geo = new QubitGeoIpHelper(
    $config['geoip_asn_db_path'] ?? '',
    $config['geoip_city_db_path'] ?? '',
    $logger,
    $proxyGeoIp
);
$userChallenge = new QubitUserChallenge(
    $config,
    $geo,
    $logger
);

// If user has 'visited' cookie, expire any challenge cookies.
if (
    !$useAltcha
    && isset($_COOKIE[$visitedCookie])
    && $_COOKIE[$visitedCookie] === $key
) {
    QubitChallengeDecisionLog::write($config, 'visited', 'legacy_visited_cookie');
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
// Allow user to bypass the challenge based on GeoIP, User-Agent etc.
if ($userChallenge->shouldBypassChallenge($remoteIp, $ua)) {
    QubitChallengeDecisionLog::write($config, 'skipped', 'configured_exception');

    return;
}

// If user passed the JS tests, grant visit and continue.
if (
    !$useAltcha
    && isset($_COOKIE[$jsCookie])
    && $_COOKIE[$jsCookie] === $key
    && $userChallenge->checkHeadless($key)
) {
    QubitChallengeDecisionLog::write($config, 'passed', 'legacy_js_verified');
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

QubitChallengeDecisionLog::write($config, 'challenged', $useAltcha ? 'clearance_required' : 'legacy_challenge_required');

// Use JS localStorage to save the originally requested URI so
// user can be redirected later.
$nonce = base64_encode(random_bytes(16));
$rawUri = json_encode($_SERVER['REQUEST_URI'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE);
header('Cache-Control: no-store');
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
