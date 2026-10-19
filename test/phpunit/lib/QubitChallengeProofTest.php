<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/QubitChallengeProof.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeProofTest extends TestCase
{
    private $directory;
    private $config;
    private $now;
    private $proof;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-proof-'.bin2hex(random_bytes(8));
        $this->config = ['salt' => str_repeat('test-only-secret-', 3), 'altcha_max_number' => 10, 'delay_seconds' => 5];
        $this->now = 1700000000;
        $this->proof = new QubitChallengeProof($this->config, $this->directory, function () { return $this->now; });
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') ?: [] as $entry) {
            rmdir($entry);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        }
    }

    public function testProofIssuesBoundClearanceAndCannotBeReplayed()
    {
        $payload = $this->solve();
        $this->assertNull($this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser'), 'Delay must be enforced on the server.');
        $this->now += 5;
        $token = $this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        $this->assertIsString($token);
        $this->assertTrue($this->proof->validClearance($token, '192.0.2.1', 'browser'));
        $this->assertFalse($this->proof->validClearance($token, '192.0.2.2', 'browser'));
        $this->assertFalse($this->proof->validClearance($token, '192.0.2.1', 'other'));
        $this->assertNull($this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser'));
        $this->now += 3600;
        $this->assertFalse($this->proof->validClearance($token, '192.0.2.1', 'browser'));
    }

    public function testMobileClearanceSurvivesIpChangesButProofStaysBound()
    {
        $config = array_merge($this->config, ['clearance_bind_to_ip' => false]);
        $mobile = new QubitChallengeProof($config, $this->directory, function () { return $this->now; });
        $payload = $this->solve();
        $this->now += 5;
        $this->assertNull($mobile->redeem($payload, 'not_bot', '192.0.2.2', 'browser'));
        $this->assertSame('client_binding_mismatch', $mobile->failureReason());
        $token = $mobile->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        $this->assertIsString($token);
        $this->assertTrue($mobile->validClearance($token, '192.0.2.1', 'browser'));
        $this->assertTrue($mobile->validClearance($token, '192.0.2.2', 'browser'));
        $this->assertTrue($mobile->validClearance($token, '2001:db8::1', 'browser'));
        $this->assertFalse($mobile->validClearance($token, '192.0.2.2', 'other-browser'));
        $this->assertFalse($mobile->validClearance($token.'x', '192.0.2.2', 'browser'));
        $rotated = new QubitChallengeProof(array_merge($config, ['salt' => str_repeat('rotated-secret-', 3)]), $this->directory, function () { return $this->now; });
        $this->assertFalse($rotated->validClearance($token, '192.0.2.2', 'browser'));
        $this->assertNull($mobile->redeem($payload, 'not_bot', '192.0.2.1', 'browser'));
        $this->assertFalse($this->proof->validClearance($token, '192.0.2.1', 'browser'), 'Strict policy rejects mobile cookies.');
        $this->now += 3600;
        $this->assertFalse($mobile->validClearance($token, '192.0.2.2', 'browser'));
    }

    public function testSwitchingToMobilePolicyRejectsExistingStrictCookies()
    {
        $payload = $this->solve();
        $this->now += 5;
        $token = $this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        $this->assertTrue($this->proof->validClearance($token, '192.0.2.1', 'browser'));
        $mobile = new QubitChallengeProof(array_merge($this->config, ['clearance_bind_to_ip' => false]), $this->directory, function () { return $this->now; });
        $this->assertFalse($mobile->validClearance($token, '192.0.2.1', 'browser'));
    }

    /**
     * @dataProvider strictBindingProvider
     *
     * @param mixed $setting
     */
    public function testOnlyBooleanFalseDisablesIpBinding($setting)
    {
        $proof = new QubitChallengeProof(array_merge($this->config, ['clearance_bind_to_ip' => $setting]), $this->directory, function () { return $this->now; });
        $payload = $this->solve();
        $this->now += 5;
        $token = $proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        $this->assertTrue($proof->validClearance($token, '192.0.2.1', 'browser'));
        $this->assertFalse($proof->validClearance($token, '192.0.2.2', 'browser'));
        $this->assertTrue($this->proof->validClearance($token, '192.0.2.1', 'browser'), 'Strict signature remains compatible with existing cookies.');
    }

    public function strictBindingProvider(): array
    {
        return [[true], [null], ['false'], [0]];
    }

    /**
     * @dataProvider botdPolicyProvider
     *
     * @param mixed $result
     */
    public function testBotdPolicyNeverReplacesProof(array $options, $result, bool $allowed)
    {
        $policy = new QubitChallengeProof(array_merge($this->config, $options), $this->directory, function () { return $this->now; });
        $this->assertNull($policy->redeem('not-a-proof', $result, '192.0.2.1', 'browser'));
        $payload = $this->solve();
        $this->now += 5;
        $token = $policy->redeem($payload, $result, '192.0.2.1', 'browser');
        if ($allowed) {
            $this->assertIsString($token);
            $this->assertTrue($policy->validClearance($token, '192.0.2.1', 'browser'));
        } else {
            $this->assertNull($token);
            $this->assertSame('botd_rejected', $policy->failureReason());
        }
    }

    public function botdPolicyProvider(): array
    {
        return [
            'default blocks automation' => [[], 'selenium', false],
            'default blocks errors' => [[], 'error', false],
            'observe permits automation after proof' => [['botd_mode' => 'observe'], 'selenium', true],
            'observe permits errors after proof' => [['botd_mode' => 'observe'], 'error', true],
            'observe rejects malformed report' => [['botd_mode' => 'observe'], [], false],
            'explicit kind allowed' => [['botd_allowed_kinds' => ['selenium']], 'selenium', true],
            'other kind still blocked' => [['botd_allowed_kinds' => ['selenium']], 'headless_chrome', false],
            'allow-list cannot allow errors' => [['botd_allowed_kinds' => ['error']], 'error', false],
            'unknown mode stays strict' => [['botd_mode' => 'typo'], 'selenium', false],
            'malformed allow-list stays strict' => [['botd_allowed_kinds' => 'selenium'], 'selenium', false],
        ];
    }

    public function testWrongBindingOrBotdCannotConsumeProof()
    {
        $payload = $this->solve();
        $this->now += 5;
        foreach ([[null, '192.0.2.1', 'browser'], ['bot', '192.0.2.1', 'browser'], [[], '192.0.2.1', 'browser'], ['not_bot', '192.0.2.2', 'browser'], ['not_bot', '192.0.2.1', 'other']] as $args) {
            $this->assertNull($this->proof->redeem($payload, ...$args));
        }
        $this->assertIsString($this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser'));
    }

    public function testExpiredProofRejected()
    {
        $payload = $this->solve();
        $this->now += 120;
        $this->assertNull($this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser'));
    }

    public function testTamperingAndMalformedPayloadsFailClosed()
    {
        $valid = json_decode(base64_decode($this->solve()), true);
        $this->now += 5;
        foreach ([
            ['algorithm' => 'SHA-512'], ['challenge' => str_repeat('0', 64)],
            ['signature' => str_repeat('0', 64)], ['salt' => $valid['salt'].'0'],
            ['number' => (string) $valid['number']], ['number' => 0.5],
            ['number' => -1], ['number' => []], ['salt' => []],
            ['test' => true], ['signature' => []], ['challenge' => []],
        ] as $change) {
            $this->assertNull($this->proof->redeem(base64_encode(json_encode(array_merge($valid, $change))), 'not_bot', '192.0.2.1', 'browser'));
        }
        foreach (['', 'invalid', base64_encode('null'), base64_encode('[]'), str_repeat('A', 8193)] as $invalid) {
            $this->assertNull($this->proof->redeem($invalid, 'not_bot', '192.0.2.1', 'browser'));
        }
    }

    public function testClearanceRejectsLegacyCookiesTamperingAndSaltRotation()
    {
        $payload = $this->solve();
        $this->now += 5;
        $token = $this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        foreach ([null, [], md5('192.0.2.1:'.$this->config['salt']), $token.'x', str_replace('v1.', 'v2.', $token)] as $invalid) {
            $this->assertFalse($this->proof->validClearance($invalid, '192.0.2.1', 'browser'));
        }
        $this->config['salt'] = str_repeat('rotated-test-secret-', 3);
        $rotated = new QubitChallengeProof($this->config, $this->directory, function () { return $this->now; });
        $this->assertFalse($rotated->validClearance($token, '192.0.2.1', 'browser'));
        $this->assertNull($rotated->redeem($this->solve(), 'not_bot', '192.0.2.1', 'browser'));
    }

    public function testReplayStorageFailureCannotGrantClearance()
    {
        $payload = $this->solve();
        $this->now += 5;
        file_put_contents($this->directory, 'not a directory');

        try {
            $this->expectException(RuntimeException::class);
            $this->proof->redeem($payload, 'not_bot', '192.0.2.1', 'browser');
        } finally {
            unlink($this->directory);
        }
    }

    public function testOnlyOneConcurrentWorkerCanRedeemAProof()
    {
        $payload = $this->solve();
        $this->now += 5;
        $code = 'require '.var_export(dirname(__DIR__, 3).'/lib/QubitChallengeProof.class.php', true).';'
            .'$proof = new QubitChallengeProof('.var_export($this->config, true).', '.var_export($this->directory, true).', static function () { return '.$this->now.'; });'
            .'echo null === $proof->redeem('.var_export($payload, true).', "not_bot", "192.0.2.1", "browser") ? "rejected" : "accepted";';
        $workers = [];
        for ($worker = 0; $worker < 8; ++$worker) {
            $process = proc_open([PHP_BINARY, '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            $workers[] = [$process, $pipes];
        }
        $accepted = 0;
        foreach ($workers as [$process, $pipes]) {
            $accepted += 'accepted' === stream_get_contents($pipes[1]) ? 1 : 0;
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
            $this->assertSame('', $errors);
        }
        $this->assertSame(1, $accepted);
    }

    public function testBotdCanBeDisabledIndependently()
    {
        $this->config['botd_enabled'] = false;
        $proof = new QubitChallengeProof($this->config, $this->directory, function () { return $this->now; });
        $payload = $this->solve();
        $this->now += 5;
        $this->assertIsString($proof->redeem($payload, null, '192.0.2.1', 'browser'));
    }

    public function testSecretMustBeConfigured()
    {
        $this->expectException(RuntimeException::class);
        new QubitChallengeProof(['salt' => 'REPLACE_WITH_SECRET'], $this->directory);
    }

    private function solve(): string
    {
        $data = $this->proof->createChallenge('192.0.2.1', 'browser');
        for ($number = 0; $number <= $data['maxnumber']; ++$number) {
            if (hash('sha256', $data['salt'].$number) === $data['challenge']) {
                // Exactly the fields returned by the real ALTCHA widget.
                unset($data['maxnumber']);
                $data['number'] = $number;

                return base64_encode(json_encode($data));
            }
        }
        $this->fail('No solution found.');
    }
}
