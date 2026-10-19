<?php

use PHPUnit\Framework\TestCase;

/**
 * Exercise the real front controller, endpoints, filter and response headers.
 *
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeHttpTest extends TestCase
{
    private $directory;
    private $server;
    private $address;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 3);
        $this->directory = sys_get_temp_dir().'/atom-proof-http-'.bin2hex(random_bytes(8));
        mkdir($this->directory.'/config', 0700, true);
        mkdir($this->directory.'/web/challenge', 0700, true);
        mkdir($this->directory.'/lib/challenge', 0700, true);
        symlink($root.'/vendor', $this->directory.'/vendor');
        foreach (['QubitChallengeProof', 'QubitChallengeDecisionLog', 'QubitChallengeConfig', 'QubitGeoIpHelper', 'QubitUserChallenge'] as $name) {
            copy($root.'/lib/'.$name.'.class.php', $this->directory.'/lib/'.$name.'.class.php');
        }
        copy($root.'/lib/challenge/filter.php', $this->directory.'/lib/challenge/filter.php');
        copy($root.'/lib/challenge/dependencies.php', $this->directory.'/lib/challenge/dependencies.php');
        foreach (['index.php', 'altcha.php'] as $name) {
            copy($root.'/web/challenge/'.$name, $this->directory.'/web/challenge/'.$name);
        }
        $front = explode('require_once dirname(__FILE__)', file_get_contents($root.'/index.php'))[0];
        file_put_contents($this->directory.'/index.php', $front.'echo "APPLICATION";');
        file_put_contents($this->directory.'/config/appChallenge.yml', "activated: true\nverification: altcha\nsalt: 'http-test-only-secret-at-least-32-characters'\ngeoip_from_nginx: true\nbotd_enabled: true\naltcha_max_number: 10\ndelay_seconds: 0\ndecision_log_enabled: true\n");
        $socket = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
        $this->assertIsResource($socket, $error);
        $this->address = stream_socket_get_name($socket, false);
        fclose($socket);
        $this->server = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', '-S', $this->address, '-t', $this->directory, $this->directory.'/index.php'], [
            0 => ['file', '/dev/null', 'r'],
            1 => ['file', $this->directory.'/server.log', 'a'],
            2 => ['file', $this->directory.'/server.log', 'a'],
        ], $pipes);
        $this->assertIsResource($this->server);
        for ($attempt = 0; $attempt < 100; ++$attempt) {
            $ready = @stream_socket_client('tcp://'.$this->address, $errno, $error, 0.02);
            if (false !== $ready) {
                fclose($ready);

                return;
            }
            usleep(20000);
        }
        $this->fail('HTTP test server did not start.');
    }

    protected function tearDown(): void
    {
        if (is_resource($this->server)) {
            proc_terminate($this->server);
            proc_close($this->server);
        }
        $this->remove($this->directory);
    }

    public function testHttpClearanceFlowAndRejectionPaths()
    {
        [$body, $headers] = $this->request('/challenge');
        $this->assertStringContainsString('Verify and continue', $body);
        $dependencies = require dirname(__DIR__, 3).'/lib/challenge/dependencies.php';
        foreach (['altcha', 'botd'] as $name) {
            $this->assertStringContainsString('import('.json_encode($dependencies[$name]['asset'], JSON_UNESCAPED_SLASHES).')', $body);
        }
        $this->assertStringNotContainsString('http-test-only-secret', $body);
        $this->assertStringNotContainsString('data-cookie-value', $body);
        $this->assertContains('Cache-Control: no-store', $headers);
        $csp = implode('', array_filter($headers, static function ($h) { return 0 === strpos($h, 'Content-Security-Policy:'); }));
        preg_match('/nonce="([^"]+)"/', $body, $nonce);
        $this->assertNotEmpty($nonce);
        $this->assertStringContainsString("'nonce-".$nonce[1]."'", $csp);
        $this->assertStringContainsString('<script type="module" nonce="'.$nonce[1].'">', $body);
        $this->assertStringContainsString('<style nonce="'.$nonce[1].'">', $body);
        $this->assertStringContainsString("worker-src 'self' blob:", $csp);
        $this->assertStringContainsString("frame-ancestors 'none'", $csp);
        $this->assertStringContainsString("base-uri 'none'", $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringNotContainsString('unsafe-eval', $csp);
        $this->assertStringContainsString("'".$dependencies['altcha']['style_hash']."'", $csp);
        [$freshPage] = $this->request('/challenge');
        preg_match('/nonce="([^"]+)"/', $freshPage, $freshNonce);
        $this->assertNotSame($nonce[1], $freshNonce[1]);
        [$body] = $this->request('/challenge-not-a-route');
        $this->assertStringContainsString('<title>Redirecting', $body, 'Similar path prefixes must not bypass the filter.');
        [$body] = $this->request('/protected', 'GET', '', ['Cookie: atom_visited='.md5('127.0.0.1:http-test-only-secret-at-least-32-characters')]);
        $this->assertStringContainsString('<title>Redirecting', $body);
        [, $headers] = $this->request('/challenge/verify');
        $this->assertStringContainsString('405', $headers[0]);
        [, $headers] = $this->request('/challenge/proof', 'GET', '', ['Sec-Fetch-Site: cross-site']);
        $this->assertStringContainsString('403', $headers[0]);
        [, $headers] = $this->request('/challenge/verify', 'POST', '{}', ['Content-Type: text/plain']);
        $this->assertStringContainsString('415', $headers[0]);
        [, $headers] = $this->request('/challenge/verify', 'POST', str_repeat('a', 13000), ['Content-Type: application/json']);
        $this->assertStringContainsString('403', $headers[0]);

        [$body] = $this->request('/challenge/proof');
        $proof = json_decode($body, true);
        $this->assertArrayNotHasKey('_solution', $proof);
        for ($number = 0; $number <= $proof['maxnumber']; ++$number) {
            if ($proof['challenge'] === hash('sha256', $proof['salt'].$number)) {
                break;
            }
        }
        $proof['number'] = $number;
        unset($proof['maxnumber']);
        $payload = base64_encode(json_encode($proof));
        [, $headers] = $this->request('/challenge/verify', 'POST', json_encode(['payload' => $payload, 'botd' => 'bot']), ['Content-Type: application/json']);
        $this->assertStringContainsString('403', $headers[0]);
        [$body, $headers] = $this->request('/challenge/verify', 'POST', json_encode(['payload' => $payload, 'botd' => 'not_bot']), ['Content-Type: application/json']);
        $this->assertSame(['verified' => true], json_decode($body, true));
        $cookie = implode('\n', array_filter($headers, static function ($header) { return 0 === strpos($header, 'Set-Cookie:'); }));
        $this->assertStringContainsString('; secure', $cookie);
        $this->assertStringContainsString('; HttpOnly', $cookie);
        $this->assertStringContainsString('; SameSite=Lax', $cookie);
        preg_match('/atom_visited=([^;]+)/', $cookie, $matches);
        $this->assertNotEmpty($matches);
        // nginx supplies REMOTE_ADDR. Forged HTTP address headers cannot alter it.
        [$body] = $this->request('/protected?test=1', 'GET', '', ['Cookie: atom_visited='.$matches[1], 'X-Forwarded-For: 203.0.113.1', 'X-Real-IP: 203.0.113.2']);
        $this->assertSame('APPLICATION', $body);
        [$body] = $this->request('/protected', 'GET', '', ['Cookie: atom_visited='.$matches[1], 'User-Agent: other-browser']);
        $this->assertStringContainsString('<title>Redirecting', $body);
        [, $headers] = $this->request('/challenge/verify', 'POST', json_encode(['payload' => $payload, 'botd' => 'not_bot']), ['Content-Type: application/json']);
        $this->assertStringContainsString('403', $headers[0], 'Redeeming the same proof twice must fail.');
        // A verified solution must still fail closed when claims cannot be stored.
        [$body] = $this->request('/challenge/proof');
        $next = json_decode($body, true);
        for ($number = 0; $number <= $next['maxnumber']; ++$number) {
            if ($next['challenge'] === hash('sha256', $next['salt'].$number)) {
                break;
            }
        }
        $next['number'] = $number;
        $this->remove($this->directory.'/cache/challenge/replay');
        file_put_contents($this->directory.'/cache/challenge/replay', 'not a directory');
        [, $headers] = $this->request('/challenge/verify', 'POST', json_encode(['payload' => base64_encode(json_encode($next)), 'botd' => 'not_bot']), ['Content-Type: application/json']);
        $this->assertStringContainsString('503', $headers[0]);
        $this->assertSame([], array_values(array_filter($headers, static function ($h) { return 0 === strpos($h, 'Set-Cookie:'); })));
        $this->assertStringNotContainsString('PHP Warning', file_get_contents($this->directory.'/server.log'));
        $events = array_map(static function ($line) { return json_decode($line, true, 512, JSON_THROW_ON_ERROR); }, file($this->directory.'/log/appChallenge.decisions.log', FILE_IGNORE_NEW_LINES));
        $reasons = array_column($events, 'reason');
        foreach (['clearance_required', 'altcha_verified', 'valid_clearance', 'botd_rejected', 'replayed_proof', 'verification_unavailable'] as $reason) {
            $this->assertContains($reason, $reasons);
        }
        $this->assertStringNotContainsString('http-test-only-secret', file_get_contents($this->directory.'/log/appChallenge.decisions.log'));
    }

    public function testBrowserReportsNeverGrantClearanceAndCanBeDisabled()
    {
        [, $headers] = $this->request('/challenge/report');
        $this->assertStringContainsString('405', $headers[0]);
        [, $headers] = $this->request('/challenge/report', 'POST', '{"reason":"botd_detected","bot_kind":"headless_chrome"}', ['Content-Type: application/json']);
        $this->assertStringContainsString('204', $headers[0]);
        $this->assertSame([], array_values(array_filter($headers, static function ($h) { return 0 === strpos($h, 'Set-Cookie:'); })));
        $file = $this->directory.'/log/appChallenge.decisions.log';
        $events = array_map(static function ($line) { return json_decode($line, true); }, file($file));
        $report = end($events);
        $this->assertSame('client_reported', $report['decision']);
        $this->assertSame('browser', $report['source']);
        $this->assertSame('botd', $report['stage']);
        $this->assertSame('headless_chrome', $report['bot_kind']);
        [$body] = $this->request('/protected');
        $this->assertStringContainsString('<title>Redirecting', $body);
        [, $headers] = $this->request('/challenge/report', 'POST', '{"reason":"altcha_verified"}', ['Content-Type: application/json']);
        $this->assertStringContainsString('400', $headers[0]);
        [, $headers] = $this->request('/challenge/report', 'POST', '{"reason":"botd_detected"}', ['Content-Type: application/json', 'Sec-Fetch-Site: cross-site']);
        $this->assertStringContainsString('403', $headers[0]);
        [, $headers] = $this->request('/challenge/report', 'POST', str_repeat('a', 1025), ['Content-Type: application/json']);
        $this->assertStringContainsString('400', $headers[0]);
        $config = $this->directory.'/config/appChallenge.yml';
        file_put_contents($config, str_replace('decision_log_enabled: true', 'decision_log_enabled: false', file_get_contents($config)));
        $before = file_get_contents($file);
        [$page] = $this->request('/challenge');
        $this->assertStringContainsString('const loggingEnabled = false;', $page);
        [, $headers] = $this->request('/challenge/report', 'POST', '{"reason":"botd_error"}', ['Content-Type: application/json']);
        $this->assertStringContainsString('204', $headers[0]);
        $this->assertSame($before, file_get_contents($file));
    }

    private function request(string $path, string $method = 'GET', string $body = '', array $headers = []): array
    {
        $headers[] = 'Connection: close';
        if (!array_filter($headers, static function ($h) { return 0 === strpos($h, 'User-Agent:'); })) {
            $headers[] = 'User-Agent: test-browser';
        }
        $context = stream_context_create(['http' => [
            'method' => $method, 'content' => $body, 'header' => implode("\r\n", $headers),
            'ignore_errors' => true, 'timeout' => 5,
        ]]);
        $response = file_get_contents('http://'.$this->address.$path, false, $context);
        $this->assertNotFalse($response);

        return [$response, $http_response_header];
    }

    private function remove(string $path): void
    {
        if (is_link($path) || is_file($path)) {
            unlink($path);
        } elseif (is_dir($path)) {
            foreach (new FilesystemIterator($path) as $item) {
                $this->remove($item->getPathname());
            }
            rmdir($path);
        }
    }
}
