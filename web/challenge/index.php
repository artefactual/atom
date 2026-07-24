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

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

// Standalone JS challenge endpoint.

// Load YAML parser and challenge config.
require_once __DIR__.'/../../vendor/composer/autoload.php';
$configFile = __DIR__.'/../../config/appChallenge.yml';

// If the js challenge feature config file is missing, the feature is disabled
// and so we should not be here (at /challenge). Return 404 error.
if (!is_readable($configFile)) {
    header('HTTP/1.1 404 Internal Server Error');

    exit;
}
$config = Yaml::parseFile($configFile);

// If the js challenge feature is disabled, return 404.
$activated = (bool) ($config['activated'] ?? false);
if (!$activated) {
    header('HTTP/1.1 404 Internal Server Error');

    exit;
}

// Determine remote IP. If not available, return 500.
$remoteIp = $_SERVER['REMOTE_ADDR'] ?? null;
if (!$remoteIp) {
    header('HTTP/1.1 500 Internal Server Error');

    exit;
}

// Compute cookie-key.
$key = md5($remoteIp.':'.($config['salt'] ?? ''));

// Cookie names & TTL.
$jsCookieName = $config['cookiename_js'] ?? 'atom_js';
$headlessCookieName = $config['cookiename_headless'] ?? 'atom_headless';

// Generate nonces for CSP.
$nonce = base64_encode(random_bytes(16));

// Send Content-Security-Policy header allowing only these inline tags via nonce.
header(
    "Content-Security-Policy: default-src 'self'; ".
    "style-src 'self' 'nonce-{$nonce}'; ".
    "script-src 'self' 'nonce-{$nonce}';"
);

// Render HTML response.
?><!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars(
        $config['dialog_title'] ?? 'Verifying Your Browser',
        ENT_QUOTES
    ); ?></title>

  <!-- Inline CSS with nonce for CSP -->
  <style nonce="<?php echo $nonce; ?>">
    html, body { height:100%; margin:0; font-family:system-ui, sans-serif; background:#f8f9fa; }
    .challenge-container { display:flex; justify-content:center; align-items:center; height:100%; padding:1rem; }
    .challenge-card { max-width:400px; width:100%; background:#fff; border-radius:.5rem;
                       padding:1.5rem; text-align:center; }
    .challenge-logo { width:100%; background:rgba(0, 0, 0, 0.3); padding:0.5rem; border-radius:0.25rem; box-sizing:border-box; }
    .challenge-logo img { display:block; margin:0 auto; width:auto; height:auto; }
    .challenge-card h2 { margin-bottom:1rem; font-size:1.5rem; color:#ff6600; }
    .challenge-card p { margin-bottom:1.5rem; color:#000; }
    .fw-bold { font-weight:700; }
    .text-danger { color:#ff6600; }
  </style>
</head>
<body>
  <div class="challenge-container">
    <div class="challenge-card">
      <!-- Logo -->
      <div class="challenge-logo">
        <img src="/images/logo.png" alt="Logo">
      </div>

      <h2>
        <i class="fa fa-check-circle"></i>
        <?php echo htmlspecialchars(
            $config['dialog_title'] ?? 'Verifying Your Browser',
            ENT_QUOTES
        ); ?>
      </h2>
      <p>
        <?php echo htmlspecialchars(
            $config['dialog_message'] ?? "Hang tight— we're just making sure you're a real person so our community stays safe.",
            ENT_QUOTES
        ); ?><br>
        <?php echo htmlspecialchars(
            $config['redirect_prefix'] ?? 'Redirecting in',
            ENT_QUOTES
        ); ?> <span id="countdown" class="fw-bold"></span> <?php echo htmlspecialchars(
            $config['redirect_suffix'] ?? 'second(s)...',
            ENT_QUOTES
        ); ?>
      </p>
      <noscript class="text-danger">
        <strong>Error:</strong> <?php echo htmlspecialchars(
            $config['noscript_message'] ?? 'Please enable JavaScript in your browser and try again.',
            ENT_QUOTES
        ); ?>
      </noscript>
      <section id="user-challenge"
               data-delay-seconds="<?php echo (int) ($config['delay_seconds'] ?? 5); ?>"
               data-cookie-name-has-js="<?php echo htmlspecialchars($jsCookieName, ENT_QUOTES); ?>"
               data-cookie-name-headless="<?php echo htmlspecialchars($headlessCookieName, ENT_QUOTES); ?>"
               data-cookie-value="<?php echo htmlspecialchars($key, ENT_QUOTES); ?>">
      </section>
    </div>
  </div>

  <script nonce="<?php echo $nonce; ?>">
  (function(){
    var el = document.getElementById('user-challenge'),
        delay = parseInt(el.dataset.delaySeconds, 10) || 5,
        jsCookie = el.dataset.cookieNameHasJs,
        headlessCookie = el.dataset.cookieNameHeadless,
        val = el.dataset.cookieValue,
        cd = document.getElementById('countdown');

    cd.textContent = delay;

    var timer = setInterval(function(){
      delay--;
      if (delay <= 0) {
        clearInterval(timer);

        // Set JS-test cookie (30s, Secure, SameSite=strict)
        var exp = new Date(Date.now() + 30000).toUTCString();
        document.cookie = jsCookie + '=' + val +
          '; expires=' + exp +
          '; path=/' +
          '; samesite=strict' +
          '; secure';

        // Set headless cookie (30s, Secure, SameSite=strict)
        var headlessValue = val + ':' + navigator.webdriver;
        var enc           = encodeURIComponent(btoa(headlessValue));
        document.cookie = headlessCookie + '=' + enc +
          '; expires=' + exp +
          '; path=/' +
          '; samesite=strict' +
          '; secure';

        // Get the saved URI and redirect.
        var orig = localStorage.getItem('atom_challenge_redirect_url') || '/';
        localStorage.removeItem('atom_challenge_redirect_url');
        window.location.href = orig;

      } else {
        cd.textContent = delay;
      }
    }, 1000);
  })();
  </script>
</body>
</html>
