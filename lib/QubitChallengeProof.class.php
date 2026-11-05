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

use AltchaOrg\Altcha\Algorithm\Pbkdf2;
use AltchaOrg\Altcha\Altcha;
use AltchaOrg\Altcha\CreateChallengeOptions;
use AltchaOrg\Altcha\Payload;
use AltchaOrg\Altcha\VerifySolutionOptions;

/**
 * ALTCHA v2 proof verification and server-only clearance, without Symfony.
 *
 * BotD is an untrusted browser signal, never a substitute for proof of work.
 */
class QubitChallengeProof
{
    private $config;
    private $directory;
    private $clock;
    private $altcha;
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
        $max = $this->option('altcha_max_counter', 1000, 1, 10000);

        // One PBKDF2 derivation on issuance; browsers search for its counter.
        // Both policy metadata and the derived-key HMAC are signed. Requiring
        // keySignature below keeps verification on the HMAC-only fast path.
        return $this->altcha()->createChallenge(new CreateChallengeOptions(
            algorithm: new Pbkdf2(),
            cost: $this->option('altcha_cost', 1000, 100, 10000),
            counter: random_int(0, $max),
            expiresAt: $expires,
            data: ['v' => 2, 'i' => $now, 'm' => $max, 'b' => $this->binding($ip, $ua)],
        ))->toArray();
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
        $data = json_decode((string) base64_decode($payload, true), true, 16);
        if (!is_array($data)
            || array_diff(array_keys($data), ['challenge', 'solution'])
            || !is_array($data['challenge'] ?? null)
            || !is_array($data['challenge']['parameters'] ?? null)
            || !is_array($data['solution'] ?? null)) {
            return null;
        }
        $challenge = $data['challenge'];
        $params = $challenge['parameters'];
        $solution = $data['solution'];
        $meta = $params['data'] ?? null;
        $now = ($this->clock)();
        $expires = $params['expiresAt'] ?? null;
        // Reject malformed or unexpected protocols before calling upstream.
        // In particular, never allow a missing keySignature to trigger KDF work
        // during verification of untrusted submissions.
        if (array_diff(array_keys($challenge), ['parameters', 'signature'])
            || array_diff(array_keys($params), ['algorithm', 'cost', 'keyLength', 'keyPrefix', 'keySignature', 'nonce', 'salt', 'expiresAt', 'data'])
            || 'PBKDF2/SHA-256' !== ($params['algorithm'] ?? null)
            || $this->option('altcha_cost', 1000, 100, 10000) !== ($params['cost'] ?? null)
            || 32 !== ($params['keyLength'] ?? null)
            || !self::isHex($params['nonce'] ?? null, 32)
            || !self::isHex($params['salt'] ?? null, 32)
            || !self::isHex($params['keyPrefix'] ?? null, 32)
            || !self::isHex($params['keySignature'] ?? null, 64)
            || !self::isHex($challenge['signature'] ?? null, 64)
            || !self::isHex($solution['derivedKey'] ?? null, 64)
            || !is_int($solution['counter'] ?? null)
            || !is_array($meta)
            || 2 !== ($meta['v'] ?? null)
            || !is_int($meta['i'] ?? null)
            || !is_int($meta['m'] ?? null)
            || !self::isHex($meta['b'] ?? null, 64)
            || !is_int($expires)
            || $meta['i'] > $now || $expires <= $meta['i']
            || $expires - $meta['i'] > $this->option('altcha_ttl_seconds', 120, 30, 600)
            || $meta['m'] < 1 || $meta['m'] > $this->option('altcha_max_counter', 1000, 1, 10000)
            || $solution['counter'] < 0 || $solution['counter'] > $meta['m']) {
            return null;
        }

        try {
            $result = $this->altcha()->verifySolution(new VerifySolutionOptions(
                payload: Payload::fromArray($data),
                algorithm: new Pbkdf2(),
            ));
        } catch (InvalidArgumentException|JsonException|TypeError|ValueError $e) {
            return null;
        }
        if (!$result->verified) {
            return $this->reject($result->expired ? 'expired_proof' : 'invalid_proof');
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
        $claim = $this->directory.'/'.$expires.'-'.$params['nonce'];
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
        return hash_hmac('sha256', $value, $this->signingKey($purpose));
    }

    private function altcha(): Altcha
    {
        if (null !== $this->altcha) {
            return $this->altcha;
        }
        // Clearance validation needs only HMACs. Load the PHP proof library
        // lazily so ordinary cleared requests retain the standalone fast path.
        // The standalone endpoints run before Symfony's Composer bootstrap.
        if (!class_exists(Altcha::class)) {
            $autoload = __DIR__.'/../vendor/composer/autoload.php';
            if (!is_readable($autoload)) {
                throw new RuntimeException('Install Composer dependencies for ALTCHA verification.');
            }

            require_once $autoload;
        }
        if (!class_exists(Altcha::class)) {
            throw new RuntimeException('The ALTCHA PHP dependency is unavailable.');
        }

        return $this->altcha = new Altcha($this->signingKey('proof-v2'), $this->signingKey('solution-v2'));
    }

    private function signingKey(string $purpose): string
    {
        return hash_hmac('sha256', 'atom-challenge-'.$purpose.'-v1', $this->config['salt'], true);
    }

    private static function isHex($value, int $length): bool
    {
        return is_string($value) && strlen($value) === $length && 1 === preg_match('/\A[0-9a-f]+\z/', $value);
    }

    private function option(string $name, int $default, int $min, int $max): int
    {
        return max($min, min($max, (int) ($this->config[$name] ?? $default)));
    }
}
