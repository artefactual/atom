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
 * ALTCHA v1 proof verification and server-only clearance, without Symfony.
 *
 * BotD is an untrusted browser signal, never a substitute for proof of work.
 */
class QubitChallengeProof
{
    private $config;
    private $directory;
    private $clock;
    private $failureReason = 'invalid_proof';

    public function __construct(array $config, string $directory, ?callable $clock = null)
    {
        $secret = $config['salt'] ?? '';
        if (!is_string($secret) || strlen($secret) < 32 || 0 === strpos($secret, 'REPLACE_WITH_')) {
            throw new RuntimeException('ALTCHA requires a random salt of at least 32 characters.');
        }
        $this->config = $config;
        $this->directory = $directory;
        $this->clock = $clock ?? static function () { return time(); };
    }

    public function createChallenge(string $ip, string $ua): array
    {
        $now = ($this->clock)();
        $expires = $now + $this->option('altcha_ttl_seconds', 120, 30, 600);
        $max = $this->option('altcha_max_number', 100000, 1, 1000000);
        // All policy metadata is covered by the solved hash and its HMAC.
        // ALTCHA's widget reads expiry from the salt query string.
        $salt = base64_encode(json_encode([
            'n' => bin2hex(random_bytes(16)),
            'i' => $now,
            'm' => $max,
            'b' => $this->binding($ip, $ua),
        ], JSON_THROW_ON_ERROR)).'?expires='.$expires;
        $challenge = hash('sha256', $salt.random_int(0, $max));

        return [
            'algorithm' => 'SHA-256',
            'challenge' => $challenge,
            'salt' => $salt,
            'maxnumber' => $max,
            'signature' => $this->sign('proof', $challenge),
        ];
    }

    /**
     * Verify proof, then atomically consume it before issuing clearance.
     * Returns null for rejected input; storage errors fail closed.
     *
     * @param mixed $botd
     */
    public function redeem(string $payload, $botd, string $ip, string $ua): ?string
    {
        $this->failureReason = 'invalid_proof';
        if (strlen($payload) > 8192) {
            return null;
        }
        $data = json_decode((string) base64_decode($payload, true), true);
        if (!is_array($data)
            || 'SHA-256' !== ($data['algorithm'] ?? null)
            || !is_string($data['challenge'] ?? null)
            || !preg_match('/\A[0-9a-f]{64}\z/', $data['challenge'])
            || !is_string($data['signature'] ?? null)
            || !is_string($data['salt'] ?? null)
            || strlen($data['salt']) > 1024
            || !is_int($data['number'] ?? null)
            || !empty($data['test'])
            || !hash_equals($this->sign('proof', $data['challenge']), $data['signature'])
            || !hash_equals($data['challenge'], hash('sha256', $data['salt'].$data['number']))) {
            return null;
        }
        $parts = explode('?expires=', $data['salt']);
        if (2 !== count($parts) || !ctype_digit($parts[1])) {
            return null;
        }
        $meta = json_decode((string) base64_decode($parts[0], true), true);
        $now = ($this->clock)();
        $expires = (int) $parts[1];
        if (!is_array($meta)
            || !is_int($meta['i'] ?? null)
            || !is_int($meta['m'] ?? null)
            || !is_string($meta['b'] ?? null)
            || !is_string($meta['n'] ?? null)
            || !preg_match('/\A[0-9a-f]{32}\z/', $meta['n'])
            || $meta['i'] > $now
            || $expires - $meta['i'] > $this->option('altcha_ttl_seconds', 120, 30, 600)
            || $data['number'] < 0 || $data['number'] > $meta['m']
            || $meta['m'] < 1 || $meta['m'] > $this->option('altcha_max_number', 100000, 1, 1000000)
) {
            return null;
        }

        if ($expires <= $now) {
            return $this->reject('expired_proof');
        }
        if ($now - $meta['i'] < $this->option('delay_seconds', 5, 0, 30)) {
            return $this->reject('proof_too_early');
        }
        if (!hash_equals($this->binding($ip, $ua), $meta['b'])) {
            return $this->reject('client_binding_mismatch');
        }
        if (!$this->acceptsBotd($botd)) {
            return $this->reject('botd_rejected');
        }

        if (!is_dir($this->directory) && !@mkdir($this->directory, 0700, true) && !is_dir($this->directory)) {
            throw new RuntimeException('Cannot create challenge replay cache.');
        }
        // A directory claim is atomic across PHP-FPM workers and creates no
        // readable proof or secret file. Never remove a live claim.
        $claim = $this->directory.'/'.$expires.'-'.$meta['n'];
        if (!@mkdir($claim, 0700)) {
            if (is_dir($claim)) {
                return $this->reject('replayed_proof');
            }

            throw new RuntimeException('Cannot consume challenge proof.');
        }

        return $this->issueClearance($ip, $ua);
    }

    public function botdMode(): string
    {
        return 'observe' === ($this->config['botd_mode'] ?? 'enforce') ? 'observe' : 'enforce';
    }

    public function allowedBotKinds(): array
    {
        $configured = $this->config['botd_allowed_kinds'] ?? [];

        return is_array($configured) ? array_values(array_filter(self::botKinds(), static function ($kind) use ($configured) { return in_array($kind, $configured, true); })) : [];
    }

    public function failureReason(): string
    {
        return $this->failureReason;
    }

    public function validClearance($token, string $ip, string $ua): bool
    {
        if (!is_string($token) || strlen($token) > 256) {
            return false;
        }
        $parts = explode('.', $token);
        $now = ($this->clock)();
        if (4 !== count($parts) || 'v1' !== $parts[0]
            || !ctype_digit($parts[1]) || !ctype_digit($parts[2])) {
            return false;
        }
        $issued = (int) $parts[1];
        $expires = (int) $parts[2];

        return $issued <= $now && $expires > $now && $expires > $issued
            && $expires - $issued <= $this->clearanceTtl()
            && hash_equals($this->clearanceSignature(implode('.', array_slice($parts, 0, 3)), $ip, $ua), $parts[3]);
    }

    public function clearanceTtl(): int
    {
        return $this->option('clearance_ttl_seconds', 3600, 60, 86400);
    }

    private static function botKinds(): array
    {
        return ['unknown', 'headless_chrome', 'phantomjs', 'nightmare', 'selenium', 'electron', 'nodejs', 'rhino', 'couchjs', 'sequentum', 'slimerjs', 'cefsharp'];
    }

    private function acceptsBotd($result): bool
    {
        if (!($this->config['botd_enabled'] ?? true)) {
            return true;
        }
        if ('not_bot' === $result) {
            return true;
        }
        if (!is_string($result)) {
            return false;
        }
        if ('observe' === $this->botdMode()) {
            return 'error' === $result || in_array($result, self::botKinds(), true);
        }

        return in_array($result, $this->allowedBotKinds(), true);
    }

    private function reject(string $reason): ?string
    {
        $this->failureReason = $reason;

        return null;
    }

    private function issueClearance(string $ip, string $ua): string
    {
        $issued = ($this->clock)();
        $value = 'v1.'.$issued.'.'.($issued + $this->clearanceTtl());

        return $value.'.'.$this->clearanceSignature($value, $ip, $ua);
    }

    private function clearanceSignature(string $value, string $ip, string $ua): string
    {
        // Only an explicit YAML false opts out. Keep the default signature
        // compatible with existing IP-bound cookies, and separate policies
        // so switching either way invalidates cookies from the other mode.
        if (false === ($this->config['clearance_bind_to_ip'] ?? true)) {
            return $this->sign('clearance-mobile', $value.'|'.$this->binding('', $ua));
        }

        return $this->sign('clearance', $value.'|'.$this->binding($ip, $ua));
    }

    private function binding(string $ip, string $ua): string
    {
        return $this->sign('binding', json_encode([$ip, $ua], JSON_INVALID_UTF8_SUBSTITUTE));
    }

    private function sign(string $purpose, string $value): string
    {
        $key = hash_hmac('sha256', 'atom-challenge-'.$purpose.'-v1', $this->config['salt'], true);

        return hash_hmac('sha256', $value, $key);
    }

    private function option(string $name, int $default, int $min, int $max): int
    {
        return max($min, min($max, (int) ($this->config[$name] ?? $default)));
    }
}
