<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/QubitChallengeDecisionLog.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeDecisionLogTest extends TestCase
{
    private $directory;
    private $server;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-decision-log-'.bin2hex(random_bytes(8));
        $this->server = $_SERVER;
        $_SERVER = [
            'REMOTE_ADDR' => '192.0.2.1', 'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/protected?token=secret-query',
            'HTTP_USER_AGENT' => "browser\nforged-line\xff", 'GEOIP_ASN' => '8075', 'GEOIP_COUNTRY' => 'CA',
            'HTTP_X_FORWARDED_FOR' => '203.0.113.1', 'HTTP_COOKIE' => 'symfony=secret-session',
            'HTTP_AUTHORIZATION' => 'Bearer secret-authorization',
        ];
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->server;
        foreach (glob($this->directory.'/*') ?: [] as $file) {
            unlink($file);
        }
        if (is_dir($this->directory)) {
            rmdir($this->directory);
        } elseif (is_file($this->directory)) {
            unlink($this->directory);
        }
    }

    public function testDisabledLoggingDoesNotCreateDirectory()
    {
        QubitChallengeDecisionLog::write(['decision_log_dir' => $this->directory], 'visited', 'valid_clearance');
        $this->assertDirectoryDoesNotExist($this->directory);
    }

    public function testRecordsAreBoundedJsonWithoutCredentialsOrQueryStrings()
    {
        $config = ['decision_log_enabled' => true, 'decision_log_dir' => $this->directory, 'salt' => 'secret-salt'];
        QubitChallengeDecisionLog::write($config, 'visited', 'valid_clearance');
        QubitChallengeDecisionLog::write($config, 'challenged', 'clearance_required');
        $file = $this->directory.'/appChallenge.decisions.log';
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $this->assertCount(2, $lines, 'Newlines in User-Agent cannot inject records.');
        $event = json_decode($lines[0], true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('visited', $event['decision']);
        $this->assertSame('valid_clearance', $event['reason']);
        $this->assertSame('192.0.2.1', $event['ip']);
        $this->assertSame('/protected', $event['path']);
        $this->assertSame('8075', $event['asn']);
        $this->assertSame('CA', $event['country']);
        $this->assertStringNotContainsString('secret-', file_get_contents($file));
        $this->assertSame(0600, fileperms($file) & 0777);
        $_SERVER['HTTP_USER_AGENT'] = str_repeat('a', 10000);
        QubitChallengeDecisionLog::write($config, 'passed', 'altcha_verified');
        $lines = file($file, FILE_IGNORE_NEW_LINES);
        $this->assertSame(512, strlen(json_decode($lines[2], true)['user_agent']));
    }

    public function testEventsDistinguishServerRejectionFromBrowserReports()
    {
        $config = ['decision_log_enabled' => true, 'decision_log_dir' => $this->directory];
        QubitChallengeDecisionLog::write($config, 'rejected', 'expired_proof');
        QubitChallengeDecisionLog::write($config, 'client_reported', 'botd_detected', 'selenium');
        QubitChallengeDecisionLog::write($config, 'client_reported', 'botd_error', 'arbitrary-secret-string');
        QubitChallengeDecisionLog::write($config, 'challenged', 'clearance_required');
        $events = array_map(static function ($line) { return json_decode($line, true); }, file($this->directory.'/appChallenge.decisions.log'));
        $this->assertSame('server', $events[0]['source']);
        $this->assertSame('altcha', $events[0]['stage']);
        $this->assertSame('browser', $events[1]['source']);
        $this->assertSame('botd', $events[1]['stage']);
        $this->assertSame('selenium', $events[1]['bot_kind']);
        $this->assertNull($events[2]['bot_kind']);
        $this->assertSame('clearance', $events[3]['stage']);
        $this->assertStringNotContainsString('arbitrary-secret-string', file_get_contents($this->directory.'/appChallenge.decisions.log'));
    }

    public function testDisabledNginxGeoIpDoesNotLogParametersAsTrustedValues()
    {
        QubitChallengeDecisionLog::write(['decision_log_enabled' => true, 'decision_log_dir' => $this->directory, 'geoip_from_nginx' => false], 'skipped', 'configured_exception');
        $event = json_decode(file_get_contents($this->directory.'/appChallenge.decisions.log'), true);
        $this->assertSame('', $event['asn']);
        $this->assertSame('', $event['country']);
    }

    public function testStorageFailureIsSilentAndDoesNotChangeResponseStatus()
    {
        file_put_contents($this->directory, 'not a directory');
        $before = http_response_code();
        $this->expectOutputString('');
        QubitChallengeDecisionLog::write(['decision_log_enabled' => true, 'decision_log_dir' => $this->directory], 'error', 'verification_unavailable');
        $this->assertSame($before, http_response_code());
    }

    public function testContendedLogDoesNotWaitForLock()
    {
        mkdir($this->directory, 0700);
        $file = $this->directory.'/appChallenge.decisions.log';
        $handle = fopen($file, 'ab');
        flock($handle, LOCK_EX);

        try {
            QubitChallengeDecisionLog::write(['decision_log_enabled' => true, 'decision_log_dir' => $this->directory], 'visited', 'valid_clearance');
            $this->assertSame('', file_get_contents($file));
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }
}
