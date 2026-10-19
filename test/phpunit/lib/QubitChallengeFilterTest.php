<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeFilterTest extends TestCase
{
    /**
     * @dataProvider nginxGeoIpProvider
     */
    public function testNginxGeoIp(array $options, array $parameters, bool $bypass)
    {
        $root = dirname(__DIR__, 3);

        require_once $root.'/vendor/symfony/lib/yaml/sfYaml.php';

        $config = array_merge([
            'activated' => true,
            'salt' => 'test-only',
            'asn_exceptions' => [8075],
            'country_exceptions' => [['country' => 'CA']],
        ], $options);
        $server = array_merge([
            'REQUEST_URI' => '/protected',
            'REMOTE_ADDR' => '192.0.2.1',
            // Client HTTP headers must never supply the trusted values.
            'HTTP_GEOIP_ASN' => '8075',
            'HTTP_GEOIP_COUNTRY' => 'CA',
            'HTTP_X_GEOIP_ASN' => '8075',
            'HTTP_X_GEOIP_COUNTRY' => 'CA',
        ], $parameters);
        $tree = [
            'config' => ['appChallenge.yml' => sfYaml::dump($config)],
            'lib' => ['challenge' => ['filter.php' => file_get_contents($root.'/lib/challenge/filter.php')]],
            'vendor' => ['symfony' => ['lib' => ['yaml' => []]]],
        ];
        foreach (['QubitChallengeConfig', 'QubitChallengeDecisionLog', 'QubitChallengeProof', 'QubitGeoIpHelper', 'QubitUserChallenge'] as $class) {
            $tree['lib'][$class.'.class.php'] = '<?php require_once '.var_export($root.'/lib/'.$class.'.class.php', true).';';
        }
        $tree['vendor']['symfony']['lib']['yaml']['sfYaml.php'] = '<?php require_once '.var_export($root.'/vendor/symfony/lib/yaml/sfYaml.php', true).';';

        // The filter exits when a challenge is required, so run it in a child.
        $code = 'require '.var_export($root.'/vendor/composer/autoload.php', true).';'
            .'org\\bovigo\\vfs\\vfsStream::setup("atom", null, '.var_export($tree, true).');'
            .'$_SERVER = '.var_export($server, true).'; $_COOKIE = [];'
            .'require "vfs://atom/lib/challenge/filter.php"; echo "BYPASSED";';
        $process = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', '-r', $code], [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors, 'No database errors or PHP warnings should be logged.');
        if ($bypass) {
            $this->assertSame('BYPASSED', $output);
        } else {
            $this->assertStringContainsString('<title>Redirecting', $output);
        }
    }

    public function nginxGeoIpProvider()
    {
        $enabled = ['geoip_from_nginx' => true];
        $auto = ['geoip_from_nginx' => 'auto'];
        $local = [
            'geoip_asn_db_path' => __DIR__.'/../../fixtures/geoip/GeoLite2-ASN-Test.mmdb',
            'geoip_city_db_path' => __DIR__.'/../../fixtures/geoip/GeoIP2-City-Test.mmdb',
        ];
        // The ASN fixture maps 1.0.0.1 to 15169.
        $localMatch = array_merge($local, ['asn_exceptions' => [15169]]);

        return [
            'ALTCHA keeps ASN exceptions' => [array_merge($enabled, ['verification' => 'altcha', 'salt' => str_repeat('test-only-', 4)]), ['GEOIP_ASN' => '8075'], true],
            'ALTCHA keeps country exceptions' => [array_merge($enabled, ['verification' => 'altcha', 'salt' => str_repeat('test-only-', 4)]), ['GEOIP_COUNTRY' => 'ca'], true],
            'ALTCHA missing GeoIP still challenges' => [array_merge($enabled, ['verification' => 'altcha', 'salt' => str_repeat('test-only-', 4)]), [], false],
            'ASN parameter, no databases' => [$enabled, ['GEOIP_ASN' => '8075'], true],
            'country parameter, no databases' => [$enabled, ['GEOIP_COUNTRY' => 'ca'], true],
            'missing parameters, spoofed headers' => [$enabled, [], false],
            'invalid parameters, spoofed headers' => [$enabled, ['GEOIP_ASN' => 'AS8075', 'GEOIP_COUNTRY' => 'CAN'], false],
            'auto detects ASN' => [$auto, ['GEOIP_ASN' => '8075'], true],
            'auto detects country' => [$auto, ['GEOIP_COUNTRY' => 'ca'], true],
            'auto detects empty ASN without local fallback' => [$auto, ['GEOIP_ASN' => ''], false],
            'auto detects empty country without local fallback' => [$auto, ['GEOIP_COUNTRY' => ''], false],
            'auto detects invalid values without local fallback' => [$auto, ['GEOIP_ASN' => 'invalid'], false],
            'auto without parameters uses local databases' => [array_merge($local, $auto), [], false],
            'auto is the default' => [[], ['GEOIP_ASN' => '8075'], true],
            'default without parameters uses local databases' => [$local, [], false],
            'auto matches local ASN without parameters' => [array_merge($localMatch, $auto), ['REMOTE_ADDR' => '1.0.0.1'], true],
            'default matches local ASN without parameters' => [$localMatch, ['REMOTE_ADDR' => '1.0.0.1'], true],
            'disabled matches local ASN despite conflicting parameter' => [array_merge($localMatch, ['geoip_from_nginx' => false]), ['REMOTE_ADDR' => '1.0.0.1', 'GEOIP_ASN' => '8075'], true],
            'parameters ignored when disabled' => [array_merge($local, ['geoip_from_nginx' => false]), ['GEOIP_ASN' => '8075', 'GEOIP_COUNTRY' => 'CA'], false],
        ];
    }
}
