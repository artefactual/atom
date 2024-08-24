<?php

use Jumbojett\OpenIDConnectClient;
use PHPUnit\Framework\TestCase;

require_once 'plugins/arOidcPlugin/config/arOidcPluginConfiguration.class.php';

/**
 * @internal
 *
 * @covers \oidcUser
 */
class OidcUserTest extends TestCase
{
    public function setUp(): void
    {
        @session_start();

        // Setup for instantiating new user.
        $this->dispatcher = new sfEventDispatcher();
        $sessionPath = sys_get_temp_dir().'/sessions_'.rand(11111, 99999);
        $this->storage = new MySessionStorage(['session_path' => $sessionPath]);
    }

    public function authenticateSuccessProvider()
    {
        return [
            'OIDC authenticate()' => [
                'redirectUrl' => 'http://127.0.0.1:63001/index.php/oidc/login',
                'providerId' => 'primary',
                'providers' => [
                    'primary' => [
                        'url' => 'https://keycloak:8443/realms/primary',
                        'client_id' => 'primary_client_id',
                        'client_secret' => 'client_secret',
                        'send_oidc_logout' => true,
                        'enable_refresh_token_use' => true,
                        'server_cert' => false,
                        'set_groups_from_attributes' => true,
                        'user_groups' => [
                            'administrator' => [
                                'attribute_value' => 'atom-admin',
                                'group_id' => 100,
                            ],
                            'editor' => [
                                'attribute_value' => 'atom-editor',
                                'group_id' => 101,
                            ],
                        ],
                        'scopes' => [
                            'openid',
                            'profile',
                            'email',
                        ],
                        'roles_source' => 'access_token',
                        'roles_path' => [
                            'realm_access',
                            'roles',
                        ],
                        'user_matching_source' => 'oidc-email',
                        'auto_create_atom_user' => true,
                    ],
                ],
                'expected' => true,
            ],
        ];
    }

    /**
     * @dataProvider authenticateSuccessProvider
     *
     * @param mixed $expected
     * @param mixed $redirectUrl
     * @param mixed $providerId
     * @param mixed $providers
     */
    public function testAuthenticateSuccess($redirectUrl, $providerId, $providers, $expected)
    {
        // $client = $this->getMockBuilder(OpenIDConnectClient::class)->setMethods(['authenticate', 'requestUserInfo', 'getIdToken', 'getVerifiedClaims'])->getMock();
        $oidcClientMock = $this->getMockBuilder(OpenIDConnectClient::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $oidcClientMock->method('authenticate')
            ->willReturn(true)
        ;

        // Mock the requestUserInfo method to return different values based on input params.
        $oidcClientMock->expects($this->atMost(2)) // Expect the method to be called twice
            ->method('requestUserInfo')
            ->withConsecutive(
                [$this->equalTo('preferred_username')],
                [$this->equalTo('email')],
            )
            ->willReturnOnConsecutiveCalls(
                'demo',
                'demo@example.com',
            )
        ;

        // Mock the getIdToken method to return a fake ID token.
        $oidcClientMock->expects($this->atMost(1))
            ->method('getIdToken')
            ->willReturn('fake-id-token-123')
        ;

        // Mock the getVerifiedClaims method to return a fake expiry time for 'exp'.
        $oidcClientMock->expects($this->atMost(1))
            ->method('getVerifiedClaims')
            ->with($this->equalTo('exp'))
            ->willReturn(1694000000)
        ;

        // Mock the getRefreshToken method to be called zero or one time.
        $oidcClientMock->expects($this->atMost(1))
            ->method('getRefreshToken')
            ->willReturn('fake-refresh-token-456')
        ;

        sfConfig::set('app_oidc_redirect_url', $redirectUrl);
        sfConfig::set('app_oidc_primary_provider_name', $providerId);
        sfConfig::set('app_oidc_providers', $providers);

        $user = new oidcUser($this->dispatcher, $this->storage);
        $user->initialize($this->dispatcher, $this->storage);
        $user->setSessionProviderId('primary');
        $user->setOidcClient($oidcClientMock);

        $result = $user->authenticate();

        $this->assertEquals($expected, $result, 'OIDC user authenticate failed.');
    }
}

class MySessionStorage extends sfSessionTestStorage
{
    public function regenerate($destroy = false)
    {
        $this->sessionId = rand(1, 9999);

        return true;
    }
}
