<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arOidcPlugin/config/arOidcPluginConfiguration.class.php';

/**
 * @internal
 *
 * @covers \arOidcPluginConfiguration
 * @covers \BaseSetting
 * @covers \BaseSettingI18n
 * @covers \QubitApcUniversalClassLoader
 * @covers \QubitQuery
 */
class ArOidcPluginConfigurationTest extends TestCase
{
    public function oidcPluginConfigurationProvider(): array
    {
        $this->sfProjectConfigurationObj = new sfProjectConfiguration();
        $this->qubitConfigrationObj = new qubitConfiguration('test', false);

        return [
            'Supply sfProjectConfiguration only' => [$this->sfProjectConfigurationObj, null, null],
            'Supply qubitConfiguration only' => [$this->qubitConfigrationObj, null, null],
            'Supply sfProjectConfiguration with params' => [$this->sfProjectConfigurationObj, 'plugins/arOidcPlugin', 'arOidcPlugin'],
            'Supply qubitConfiguration with params' => [$this->qubitConfigrationObj, 'plugins/arOidcPlugin', 'arOidcPlugin'],
        ];
    }

    /**
     * @dataProvider oidcPluginConfigurationProvider
     *
     * @param mixed $obj
     * @param mixed $rootDir
     * @param mixed $name
     */
    public function testInitializeWithProjectConfiguration($obj, $rootDir, $name)
    {
        $this->pluginConfiguration = new arOidcPluginConfiguration($obj, $rootDir, $name);
        $this->pluginConfiguration->initialize();

        $this->assertTrue($this->pluginConfiguration instanceof arOidcPluginConfiguration, 'Plugin object is not of type arOidcPluginConfiguration.');
    }
}
