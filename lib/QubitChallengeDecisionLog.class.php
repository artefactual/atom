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

/**
 * Optional, best-effort challenge diagnostics. Never logs credentials or proofs.
 */
class QubitChallengeDecisionLog
{
    public static function write(array $config, string $decision, string $reason, ?string $botKind = null): void
    {
        if (true !== ($config['decision_log_enabled'] ?? false)) {
            return;
        }
        // No GeoIP lookups: include only the existing trusted FastCGI values.
        $geoEnabled = false !== ($config['geoip_from_nginx'] ?? 'auto');
        $line = json_encode([
            'timestamp' => gmdate('c'),
            'decision' => $decision,
            'reason' => $reason,
            'stage' => self::stage($decision, $reason),
            'source' => 'client_reported' === $decision ? 'browser' : 'server',
            'bot_kind' => in_array($botKind, ['unknown', 'headless_chrome', 'phantomjs', 'nightmare', 'selenium', 'electron', 'nodejs', 'rhino', 'couchjs', 'sequentum', 'slimerjs', 'cefsharp'], true) ? $botKind : null,
            'ip' => substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 64),
            'method' => substr($_SERVER['REQUEST_METHOD'] ?? 'GET', 0, 16),
            // Query strings can contain search terms and tokens. Omit them.
            'path' => substr((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH), 0, 2048),
            'user_agent' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
            'asn' => $geoEnabled ? substr($_SERVER['GEOIP_ASN'] ?? '', 0, 32) : '',
            'country' => $geoEnabled ? substr($_SERVER['GEOIP_COUNTRY'] ?? '', 0, 2) : '',
        ], JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE)."\n";
        $root = dirname(__DIR__);
        $directory = str_replace('%SF_ROOT_DIR%', $root, $config['decision_log_dir'] ?? $root.'/log');
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            return;
        }
        // A fixed filename keeps the format simple and works with logrotate.
        $mask = umask(0077);

        try {
            $handle = @fopen(rtrim($directory, '/').'/appChallenge.decisions.log', 'ab');
        } finally {
            umask($mask);
        }
        if (false === $handle) {
            return;
        }
        // Logging must not queue PHP workers behind a slow/contended disk.
        if (@flock($handle, LOCK_EX | LOCK_NB)) {
            @fwrite($handle, $line);
            @flock($handle, LOCK_UN);
        }
        fclose($handle);
    }

    private static function stage(string $decision, string $reason): string
    {
        if (0 === strpos($reason, 'botd_')) {
            return 'botd';
        }
        if (in_array($reason, ['invalid_proof', 'expired_proof', 'proof_too_early', 'client_binding_mismatch', 'replayed_proof', 'altcha_verified', 'altcha_error'], true)) {
            return 'altcha';
        }
        if (0 === strpos($reason, 'legacy_')) {
            return 'legacy';
        }
        if ('visited' === $decision || 'challenged' === $decision) {
            return 'clearance';
        }
        if ('skipped' === $decision) {
            return 'exception';
        }

        return 'error' === $decision ? 'system' : 'request';
    }
}
