<?php

/**
 * sfGearman tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(18, new lime_output_color());

$t->diag('sfGearman');

$server1 = sfGearman::getServer('test1');
$t->is($server1, '192.168.0.1', '::getServer() config');

try
{
  sfGearman::getServer('nimportenawak');
  $t->fail('::getServer() non existant key raises an exception');
}
catch(sfConfigurationException $e)
{
  $t->pass('::getServer() non existant key raises an exception');
}

$worker1 = sfGearman::getWorker('test');
$t->is_deeply($worker1, array('md5' => array('TestWorker', 'md5')), '::getWorker() config');

try
{
  sfGearman::getWorker('nimportenawak');
  $t->fail('::getWorker() non existant key raises an exception');
}
catch(sfConfigurationException $e)
{
  $t->pass('::getWorker() non existant key raises an exception');
}

$doctrine1 = sfGearman::getDoctrine('test');
$t->is_deeply($doctrine1, array('TestArticle' => array('publish', null)), '::getDoctrine() config');

try
{
  sfGearman::getDoctrine('nimportenawak');
  $t->fail('::getDoctrine() non existant key raises an exception');
}
catch(sfConfigurationException $e)
{
  $t->pass('::getDoctrine() non existant key raises an exception');
}

class testGearmanConnection
{
  public $servers = array();
  public $options = 0;

  public function addServer($host = GEARMAN_DEFAULT_TCP_HOST, $port = GEARMAN_DEFAULT_TCP_PORT) { $this->servers[] = compact('host', 'port'); }
  public function addServers($servers = 'default:default') { $this->servers[] = $servers; }

  public function addOptions($options)    { $this->options |= $options; }
  public function setOptions($options)    { $this->options = $options; }
  public function removeOptions($options) { $this->options ^= $options; }
  public function options()               { return $this->options; }
}

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, null);
$t->is_deeply($conn->servers, array('127.0.0.1'), '::setupConnection() null use default key');

// reset gearman.yml default server
unset(sfGearman::$config['server']['default']);

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, null);
$t->is_deeply($conn->servers, array(array('host' => GEARMAN_DEFAULT_TCP_HOST, 'port' => GEARMAN_DEFAULT_TCP_PORT)), '::setupConnection() empty default key configure connection with module defaults');

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, 'test1');
$t->is_deeply($conn->servers, array('192.168.0.1'), '::setupConnection() config test1 : string');

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, 'test2');
$t->is_deeply($conn->servers, array(array('host' => '192.168.0.1', 'port' => GEARMAN_DEFAULT_TCP_PORT)), '::setupConnection() config test2 : no port');

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, 'test3');
$t->is_deeply($conn->servers, array(array('host' => '127.0.0.1', 'port' => 1111)), '::setupConnection() config test3 : host and port');

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, 'test4');
$t->is_deeply($conn->servers, array(array('host' => '192.168.0.1', 'port' => GEARMAN_DEFAULT_TCP_PORT), array('host' => '192.168.0.2', 'port' => GEARMAN_DEFAULT_TCP_PORT)), '::setupConnection() config test4 : array of array');

$conn = new testGearmanConnection;
sfGearman::setupConnection($conn, 'test5');
$t->is_deeply($conn->servers, array('192.168.0.1:1111', '192.168.0.2:2222', array('host' => '192.168.0.3', 'port' => 3333)), '::setupConnection() server config test5 : mixed');

$s = 'scalar';
$t->is(sfGearman::serialize($s), $s, '::serialize() do not modify scalar');
$a = array('not' => 'scalar');
$t->is(sfGearman::serialize($a), serialize($a), '::serialize() serialize not scalar');

$t->is(sfGearman::unserialize($s), $s, '::unserialize() do not modify scalar');
$t->is_deeply(sfGearman::unserialize($a), $a, '::unserialize() do not modify not scalar');
$t->is_deeply(sfGearman::unserialize(serialize($a)), $a, '::unserialize() unserialize serialized not scalar');

