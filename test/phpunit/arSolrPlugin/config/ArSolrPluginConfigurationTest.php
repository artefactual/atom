<?php

use PHPUnit\Framework\TestCase;

require_once 'plugins/arSolrPlugin/config/arSolrPluginConfiguration.class.php';

/**
 * @internal
 *
 * @covers \arSolrPluginConfiguration
 * @covers \BaseSetting
 * @covers \BaseSettingI18n
 * @covers \QubitApcUniversalClassLoader
 * @covers \QubitQuery
 * @covers \arSolrConfigHandler
 */
class ArSolrPluginConfigurationTest extends TestCase
{
    protected $pluginConfiguration;

    public function solrPluginConfigurationProvider(): array
    {
        $this->sfProjectConfigurationObj = new sfProjectConfiguration();
        $this->qubitConfigrationObj = new qubitConfiguration('test', false);

        return [
            'Supply sfProjectConfiguration only' => [$this->sfProjectConfigurationObj, null, null],
            'Supply qubitConfiguration only' => [$this->qubitConfigrationObj, null, null],
            'Supply sfProjectConfiguration with params' => [$this->sfProjectConfigurationObj, 'plugins/arSolrPlugin', 'arSolrPlugin'],
            'Supply qubitConfiguration with params' => [$this->qubitConfigrationObj, 'plugins/arSolrPlugin', 'arSolrPlugin'],
        ];
    }

    /**
     * @dataProvider solrPluginConfigurationProvider
     *
     * @param mixed $obj
     * @param mixed $rootDir
     * @param mixed $name
     */
    public function testInitializeWithProjectConfiguration($obj, $rootDir, $name)
    {
        $this->pluginConfiguration = new arSolrPluginConfiguration($obj, $rootDir, $name);
        $this->pluginConfiguration->initialize();

        $this->assertTrue($this->pluginConfiguration instanceof arSolrPluginConfiguration, 'Assert plugin object is arSolrPluginConfiguration');

        $this->assertTrue(is_array(arSolrPluginConfiguration::$config), 'Assert plugin config result is array');
    }
}
