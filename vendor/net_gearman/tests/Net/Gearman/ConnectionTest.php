<?php

/**
 * Net_Gearman_ConnectionTest.
 *
 * @group functional
 */
class Net_Gearman_ConnectionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @group unit
     */
    public function testSetServerVersion() {
        $mock_manager = new class extends \Net_Gearman_Manager {
            public $mock_version;
            public function __construct() {
                // noop
            }
            public function version() {
                return $this->mock_version;
            }
        };

        $connection = new class extends Net_Gearman_Connection {
            public function setServerVersion($host, $manager = null) {
                parent::setServerVersion($host, $manager);
                return $this->serverVersion;
            }
        };

        $mock_manager->mock_version = '1.1.18';

        $result = $connection->setServerVersion('localhost:4730', $mock_manager);
        $this->assertEquals('1.1.18', $result);

        $mock_manager->mock_version = '1.1.19';

        $result = $connection->setServerVersion('localhost:4730', $mock_manager);
        $this->assertEquals('1.1.19', $result);
    }

    /**
     * @group unit
     */
    public function testFixTimeout() {
        $connection = new class extends Net_Gearman_Connection {
            public $serverVersion;
            public function fixTimeout($params) {
                return parent::fixTimeout($params);
            }
        };

        $connection->serverVersion = '1.1.18';
        $result = $connection->fixTimeout(['timeout' => 10]);
        $this->assertEquals(['timeout' => 10], $result);

        $connection->serverVersion = '1.1.19';
        $result = $connection->fixTimeout(['timeout' => 10]);
        $this->assertEquals(['timeout' => 10000], $result);

        $connection->serverVersion = '1.1.19.1';
        $result = $connection->fixTimeout(['timeout' => 10]);
        $this->assertEquals(['timeout' => 10000], $result);
    }

    /**
     * When no server is supplied, it should connect to localhost:4730.
     * @group functional
     * @return void
     */
    public function testDefaultConnect()
    {
        $connection = new Net_Gearman_Connection(NET_GEARMAN_TEST_SERVER);
        if (version_compare(PHP_VERSION, '8.0.0') >= 0) {
            // PHP 8+ returns a Socket class instead of a resource now
            $this->assertInstanceOf('Socket', $connection->socket);
        } else {
            $this->assertEquals('socket', strtolower(get_resource_type($connection->socket)));
        }

        $this->assertTrue($connection->isConnected());

        $connection->close();
    }

    /**
     * 001-echo_req.phpt
     * @group functional
     * @return void
     */
    public function testSend()
    {
        $connection = new Net_Gearman_Connection(NET_GEARMAN_TEST_SERVER);
        $this->assertTrue($connection->isConnected());
        $connection->send('echo_req', ['text' => 'foobar']);

        do {
            $ret = $connection->read();
        } while (is_array($ret) && ! count($ret));

        $connection->close();

        $this->assertIsArray($ret);
        $this->assertEquals('echo_res', $ret['function']);
        $this->assertEquals(17, $ret['type']);
        $this->assertIsArray($ret['data']);

        $this->assertEquals('foobar', $ret['data']['text']);
    }
}
