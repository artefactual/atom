<?php
/**
 * Net_Gearman_ConnectionTest
 *
 * PHP version 5
 *
 * @category   Testing
 * @package    Net_Gearman
 * @subpackage Net_Gearman_Connection
 * @author     Till Klampaeckel <till@php.net>
 * @license    http://www.opensource.org/licenses/bsd-license.php New BSD License
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Net_Gearman
 * @since      0.2.4
 */
class Net_Gearman_ConnectionTest extends PHPUnit_Framework_TestCase
{
    /**
     * When no server is supplied, it should connect to localhost:4730.
     *
     * @return void
     */
    public function testDefaultConnect()
    {
        $connection = Net_Gearman_Connection::connect();
        $this->assertType('resource', $connection);
        $this->assertEquals('socket', strtolower(get_resource_type($connection)));

        $this->assertTrue(Net_Gearman_Connection::isConnected($connection));

        Net_Gearman_Connection::close($connection);
    }

    /**
     * 001-echo_req.phpt
     *
     * @return void
     */
    public function testSend()
    {
        $connection = Net_Gearman_Connection::connect();
        Net_Gearman_Connection::send($connection, 'echo_req', array('text' => 'foobar'));

        do {
            $ret = Net_Gearman_Connection::read($connection);
        } while (is_array($ret) && !count($ret));

        Net_Gearman_Connection::close($connection);

        $this->assertType('array', $ret);
        $this->assertEquals('echo_res', $ret['function']);
        $this->assertEquals(17, $ret['type']);

        $this->assertType('array', $ret['data']);
        $this->assertEquals('foobar', $ret['data']['text']);
    }
}