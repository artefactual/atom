<?php

// Included by index.php after configuration and client-address checks.
require_once __DIR__.'/../../lib/QubitChallengeProof.class.php';

require_once __DIR__.'/../../lib/QubitChallengeDecisionLog.class.php';
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

try {
    $proof = new QubitChallengeProof($config, __DIR__.'/../../cache/challenge/replay');
    if (in_array($path, ['/challenge/proof', '/challenge/verify', '/challenge/report'], true)) {
        header('Content-Type: application/json');
        $expectedMethod = '/challenge/proof' === $path ? 'GET' : 'POST';
        if ($method !== $expectedMethod) {
            header('Allow: '.$expectedMethod);
            QubitChallengeDecisionLog::write($config, 'rejected', 'method_not_allowed');
            http_response_code(405);
            echo '{"error":"method_not_allowed"}';

            return;
        }
        if ('cross-site' === ($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '')) {
            QubitChallengeDecisionLog::write($config, 'rejected', 'cross_site_request');
            http_response_code(403);
            echo '{"error":"cross_site_request"}';

            return;
        }
        if ('GET' === $method) {
            echo json_encode($proof->createChallenge($remoteIp, $ua), JSON_THROW_ON_ERROR);

            return;
        }
        if ('application/json' !== strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]))) {
            QubitChallengeDecisionLog::write($config, 'rejected', 'json_required');
            http_response_code(415);
            echo '{"error":"json_required"}';

            return;
        }
        // Bound allocation even if CONTENT_LENGTH is absent or dishonest.
        $limit = '/challenge/report' === $path ? 1024 : 12288;
        $body = file_get_contents('php://input', false, null, 0, $limit + 1);
        $data = strlen($body) <= $limit ? json_decode($body, true) : null;
        if ('/challenge/report' === $path) {
            // Diagnostic self-reports never verify a proof or issue clearance.
            $reason = is_array($data) ? ($data['reason'] ?? null) : null;
            if (!in_array($reason, ['botd_detected', 'botd_error', 'altcha_error', 'verification_error'], true)) {
                QubitChallengeDecisionLog::write($config, 'rejected', 'invalid_report');
                http_response_code(400);
                echo '{"error":"invalid_report"}';

                return;
            }
            $kind = is_string($data['bot_kind'] ?? null) ? $data['bot_kind'] : null;
            QubitChallengeDecisionLog::write($config, 'client_reported', $reason, $kind);
            http_response_code(204);

            return;
        }
        $token = null;
        $reason = 'invalid_request';
        if (is_array($data) && is_string($data['payload'] ?? null)) {
            $token = $proof->redeem($data['payload'], $data['botd'] ?? null, $remoteIp, $ua);
            $reason = $proof->failureReason();
        }
        if (null === $token) {
            QubitChallengeDecisionLog::write($config, 'rejected', $reason, is_string($data['botd'] ?? null) ? $data['botd'] : null);
            http_response_code(403);
            echo '{"error":"verification_failed"}';

            return;
        }
        setcookie($config['cookiename_visited'] ?? 'atom_visited', $token, [
            'expires' => time() + $proof->clearanceTtl(),
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        QubitChallengeDecisionLog::write($config, 'passed', 'altcha_verified', is_string($data['botd'] ?? null) ? $data['botd'] : null);
        echo '{"verified":true}';

        return;
    }
    if ('GET' !== $method) {
        header('Allow: GET');
        http_response_code(405);

        return;
    }
} catch (RuntimeException $e) {
    QubitChallengeDecisionLog::write($config, 'error', 'verification_unavailable');
    // Misconfiguration or replay-storage failure must never grant clearance.
    http_response_code(503);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Browser verification is temporarily unavailable. Please try again later.';

    return;
}

$dependencies = require __DIR__.'/../../lib/challenge/dependencies.php';
$altchaStyleHash = $dependencies['altcha']['style_hash'];
$nonce = base64_encode(random_bytes(16));
// The pinned ALTCHA widget injects a static stylesheet. Permit only its hash.
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'nonce-{$nonce}'; style-src 'self' 'nonce-{$nonce}' '{$altchaStyleHash}'; worker-src 'self' blob:; object-src 'none'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'");
$escape = static function ($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); };
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo $escape($config['dialog_title'] ?? 'Verifying Your Browser'); ?></title>
  <style nonce="<?php echo $nonce; ?>">
    body { margin:0; padding:1rem; min-height:90vh; display:grid; place-items:center;
      font-family:system-ui,sans-serif; background:#f8f9fa; color:#222; }
    main { box-sizing:border-box; width:100%; max-width:28rem; padding:2rem;
      background:white; border-radius:.5rem; text-align:center; }
    button { padding:.75rem 1.25rem; font:inherit; cursor:pointer; }
    altcha-widget { display:block; margin:1rem auto; max-width:260px; }
    #status { min-height:3rem; } [hidden] { display:none !important; }
  </style>
</head>
<body>
<main>
  <img src="/images/logo.png" alt="AtoM">
  <h1><?php echo $escape($config['dialog_title'] ?? 'Verifying Your Browser'); ?></h1>
  <p><?php echo $escape($config['dialog_message'] ?? 'Please verify your browser to continue.'); ?></p>
  <button id="verify" type="button" disabled>Verify and continue</button>
  <altcha-widget id="proof" challengeurl="/challenge/proof" workers="2" hidden></altcha-widget>
  <p id="status" role="status" aria-live="polite">Loading browser verification…</p>
  <button id="retry" type="button" hidden>Try again</button>
  <noscript><?php echo $escape($config['noscript_message'] ?? 'JavaScript is required to continue.'); ?></noscript>
</main>
<script type="module" nonce="<?php echo $nonce; ?>">
const button = document.getElementById('verify');
const retry = document.getElementById('retry');
const status = document.getElementById('status');
const widget = document.getElementById('proof');
const loggingEnabled = <?php echo json_encode(true === ($config['decision_log_enabled'] ?? false)); ?>;
const botdEnabled = <?php echo json_encode((bool) ($config['botd_enabled'] ?? true)); ?>;
const botdMode = <?php echo json_encode($proof->botdMode()); ?>;
const allowedBotKinds = <?php echo json_encode($proof->allowedBotKinds()); ?>;
const minimumDelay = <?php echo max(0, min(30, (int) ($config['delay_seconds'] ?? 5))); ?> * 1000;
let detector;
let verdict;
let started;
let submitting = false;
let reportSent = false;
function report(reason, botKind) {
    if (loggingEnabled && !reportSent) {
        reportSent = true;
        // Best effort only. Reports are untrusted and never grant clearance.
        fetch('/challenge/report', {
            method: 'POST', credentials: 'same-origin', keepalive: true,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({reason, bot_kind: botKind})
        }).catch(() => {});
    }
}
function fail(reason, botKind) {
    report(reason, botKind);
    status.textContent = 'Verification could not be completed. Please try again.';
    button.disabled = true;
    retry.hidden = false;
}
function destination() {
    let value = '/';
    try {
        value = localStorage.getItem('atom_challenge_redirect_url') || '/';
        localStorage.removeItem('atom_challenge_redirect_url');
    } catch (_) {}
    try {
        const url = new URL(value, location.origin);
        // Only return to this site, and never back into the challenge.
        if (url.origin === location.origin && !url.pathname.startsWith('/challenge')) {
            return url.pathname + url.search + url.hash;
        }
    } catch (_) {}
    return '/';
}
retry.addEventListener('click', () => location.reload());
widget.addEventListener('statechange', event => {
    if (event.detail.state === 'error' || event.detail.state === 'expired') fail('altcha_error');
});
widget.addEventListener('verified', async event => {
    if (submitting) return;
    submitting = true;
    try {
        // The server independently enforces the delay from proof issuance.
        await new Promise(resolve => setTimeout(resolve, Math.max(0, minimumDelay - (Date.now() - started) + 1000)));
        const response = await fetch('/challenge/verify', {
            method: 'POST', credentials: 'same-origin', cache: 'no-store',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({payload: event.detail.payload, botd: verdict})
        });
        if (!response.ok || (await response.json()).verified !== true) throw new Error();
        location.replace(destination());
    } catch (_) { fail('verification_error'); }
});
button.addEventListener('click', async () => {
    button.disabled = true;
    status.textContent = 'Verifying your browser…';
    if (detector) {
        try {
            const result = await detector.detect();
            verdict = result.bot === false ? 'not_bot' : (result.botKind || 'unknown');
            if (verdict !== 'not_bot') {
                report('botd_detected', verdict);
                if (botdMode !== 'observe' && !allowedBotKinds.includes(verdict)) {
                    fail('botd_detected', verdict); return;
                }
            }
        } catch (_) {
            report('botd_error');
            if (botdMode !== 'observe') { fail('botd_error'); return; }
            verdict = 'error';
        }
    }
    try {
        started = Date.now();
        widget.hidden = false;
        await widget.verify();
    } catch (_) { fail('altcha_error'); }
});
let loading = 'altcha';
try {
    await import(<?php echo json_encode($dependencies['altcha']['asset'], JSON_UNESCAPED_SLASHES); ?>);
    await customElements.whenDefined('altcha-widget');
    // Start the delay after the challenge response arrives, so a slow
    // connection cannot submit before the server's minimum proof age.
    widget.customfetch = async (url, options) => {
        const response = await fetch(url, {...options, cache: 'no-store'});
        started = Date.now();
        return response;
    };
    if (botdEnabled) {
        loading = 'botd';
        const botd = await import(<?php echo json_encode($dependencies['botd']['asset'], JSON_UNESCAPED_SLASHES); ?>);
        detector = await botd.load({monitoring: false});
    }
    button.disabled = false;
    status.textContent = 'Select Verify and continue to proceed.';
} catch (_) {
    if (loading === 'botd' && botdMode === 'observe') {
        report('botd_error');
        detector = null;
        verdict = 'error';
        button.disabled = false;
        status.textContent = 'Select Verify and continue to proceed.';
    } else {
        fail(loading === 'botd' ? 'botd_error' : 'altcha_error');
    }
}
</script>
</body>
</html>
