<?php

use Jumbojett\OpenIDConnectClient;
use PHPUnit\Framework\TestCase;

require_once 'plugins/arOidcPlugin/lib/arOidc.class.php';

/**
 * @internal
 *
 * @covers \arOidc
 * @covers \QubitApcUniversalClassLoader::findFile
 */
class ArOidcTest extends TestCase
{
    public function getOidcInstanceProvider(): array
    {
        return [
            'OIDC redirect URL defined' => [
                'redirectUrl' => 'http://127.0.0.1:63001/index.php/oidc/login',
                'expected' => 'http://127.0.0.1:63001/index.php/oidc/login',
            ],
        ];
    }

    /**
     * @dataProvider getOidcInstanceProvider
     *
     * @param mixed $expected
     * @param mixed $redirectUrl
     */
    public function testGetOidcInstance($redirectUrl, $expected)
    {
        sfConfig::set('app_oidc_redirect_url', $redirectUrl);

        $client = arOidc::getOidcInstance();
        $this->assertTrue($client instanceof OpenIDConnectClient, sprintf('Unexpected OIDC client class type: %s.', get_class($client)));

        $redirectUrl = $client->getRedirectURL();
        $this->assertSame($expected, $redirectUrl, 'OIDC redirect URL not set correctly.');
    }

    public function getOidcInstanceExceptionProvider(): array
    {
        return [
            'OIDC set empty redirect URL' => [
                'redirectUrl' => '',
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid OIDC redirect URL. Please review the app_oidc_redirect_url parameter in plugin app.yml.',
            ],
        ];
    }

    /**
     * @dataProvider getOidcInstanceExceptionProvider
     *
     * @param mixed $redirectUrl
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testGetOidcInstanceException($redirectUrl, $expectedException, $expectedExceptionMessage)
    {
        sfConfig::set('app_oidc_redirect_url', $redirectUrl);

        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        arOidc::getOidcInstance();
    }

    public function validateScopesProvider()
    {
        return [
            'OIDC scopes' => [
                'scopes' => ['one', 'two'],
                'expected' => ['one', 'two'],
            ],
            'OIDC scopes with extra spaces' => [
                'scopes' => ['one  ', '  two'],
                'expected' => ['one', 'two'],
            ],
        ];
    }

    /**
     * @dataProvider validateScopesProvider
     *
     * @param mixed $scopes
     * @param mixed $expected
     */
    public function testValidateScopes($scopes, $expected)
    {
        $result = arOidc::validateScopes($scopes);

        $this->assertSame($expected, $result, 'OIDC scopes not set correctly.');
    }

    public function validateScopesExceptionProvider()
    {
        return [
            'OIDC validate scopes when scopes array is empty' => [
                'scopes' => [],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid OIDC scopes. The app_oidc_scopes array is empty in the plugin app.yml.',
            ],
            'OIDC validate scopes when scopes has empty elements' => [
                'scopes' => ['  ', ''],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid scope value found in app_oidc_scopes',
            ],
            'OIDC validate scopes when scopes has some empty elements' => [
                'scopes' => ['one', ''],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid scope value found in app_oidc_scopes',
            ],
            'OIDC validate scopes when scopes has some empty elements' => [
                'scopes' => ['  ', 'two '],
                'expectedException' => '\Exception',
                'expectedExceptionMessage' => 'Invalid scope value found in app_oidc_scopes',
            ],
        ];
    }

    /**
     * @dataProvider validateScopesExceptionProvider
     *
     * @param mixed $scopes
     * @param mixed $expectedException
     * @param mixed $expectedExceptionMessage
     */
    public function testValidateScopesException($scopes, $expectedException, $expectedExceptionMessage)
    {
        $this->expectException($expectedException);
        $this->expectExceptionMessage($expectedExceptionMessage);

        arOidc::validateScopes($scopes);
    }

    public function validateRolesSourceProvider()
    {
        return [
            'OIDC access-token' => [
                '$tokenName' => 'access-token',
                'expected' => true,
            ],
            'OIDC id-token' => [
                '$tokenName' => 'id-token',
                'expected' => true,
            ],
            'OIDC verified-claims' => [
                '$tokenName' => 'verified-claims',
                'expected' => true,
            ],
            'OIDC user-info' => [
                '$tokenName' => 'user-info',
                'expected' => true,
            ],
            'OIDC test' => [
                '$tokenName' => 'test',
                'expected' => false,
            ],
            'OIDC empty' => [
                '$tokenName' => '',
                'expected' => false,
            ],
            'OIDC blankspace' => [
                '$tokenName' => '  ',
                'expected' => false,
            ],
            'OIDC null' => [
                '$tokenName' => null,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider validateRolesSourceProvider
     *
     * @param mixed $tokenName
     * @param mixed $expected
     */
    public function testValidateRolesSource($tokenName, $expected)
    {
        $result = arOidc::validateRolesSource($tokenName);

        $this->assertSame($expected, $result, 'OIDC arOidc::validateRolesSource returned unexpected value.');
    }

    public function validateUserMatchingSourceProvider()
    {
        return [
            'OIDC oidc-email' => [
                '$matchingSource' => 'oidc-email',
                'expected' => true,
            ],
            'OIDC oidc-username' => [
                '$matchingSource' => 'oidc-username',
                'expected' => true,
            ],
            'OIDC test' => [
                '$matchingSource' => 'test',
                'expected' => false,
            ],
            'OIDC blank' => [
                '$matchingSource' => '',
                'expected' => false,
            ],
            'OIDC blankspace' => [
                '$matchingSource' => '  ',
                'expected' => false,
            ],
            'OIDC null' => [
                '$matchingSource' => null,
                'expected' => false,
            ],
        ];
    }

    /**
     * @dataProvider validateUserMatchingSourceProvider
     *
     * @param mixed $matchingSource
     * @param mixed $expected
     */
    public function testValidateUserMatchingSource($matchingSource, $expected)
    {
        $result = arOidc::validateUserMatchingSource($matchingSource);

        $this->assertSame($expected, $result, 'OIDC arOidc::validateUserMatchingSource returned unexpected value.');
    }
}
