<?php

/**
 * sfGearmanClient tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(17, new lime_output_color());

$t->diag('sfGearmanClient');

$client = sfGearmanClient::getInstance();
$t->isa_ok($client, 'sfGearmanClient', '::getInstance() returns a sfGearmanClient object');
$t->ok($client instanceof GearmanClient, 'sfGearmanClient extends GearmanClient');

$clientbis = sfGearmanClient::getInstance();
$t->ok($client === $clientbis, '::getInstance() returns a singleton for same server');

$client2 = sfGearmanClient::getInstance('test2');
$client3 = sfGearmanClient::getInstance('test3');
$t->ok($client2 !== $client3, '::getInstance() returns an object by server');

try
{
  $client3->task('no');
  $t->fail('sfGearmanException if cant connect');
}
catch(sfGearmanException $e)
{
  $t->pass('sfGearmanException if cant connect');
}

class testoptsClient extends sfGearmanClient
{
  public function doBackground()
  {
    $GLOBALS['t']->pass('->background() do a background job');
  }
  public function doLowBackground()
  {
    $GLOBALS['t']->pass('->background() with low priority');
  }
  public function doHighBackground()
  {
    $GLOBALS['t']->pass('->background() with high priority');
  }
  public function doLow()
  {
    $GLOBALS['t']->pass('->task() with low priority');
  }
  public function doHigh()
  {
    $GLOBALS['t']->pass('->task() with high priority');
  }
}

$clientt = new testoptsClient;
$clientt->background('');
$clientt->background('', '', sfGearman::LOW);
$clientt->background('', '', sfGearman::HIGH);
$clientt->task('', '', sfGearman::LOW);
$clientt->task('', '', sfGearman::HIGH);

function test_echo($job, $data = null) { return $job->workload(); }
function test_fail($job, $data = null) { $job->sendFail(); }
function test_data($job, $data = null) { $job->sendData('data'); return $job->workload(); }
function test_warning($job, $data = null) { $job->sendWarning('oops'); return $job->workload(); }
function test_status($job, $data = null) { $job->sendStatus(1,2); $job->sendStatus(2,2); return $job->workload(); }

$worker = new sfGearmanWorker;
$worker->addFunction('test_echo', 'test_echo');
$worker->addFunction('test_fail', 'test_fail');
$worker->addFunction('test_data', 'test_data');
$worker->addFunction('test_warning', 'test_warning');
$worker->addFunction('test_status', 'test_status');

$pid = pcntl_fork();
if ($pid == -1)
{
  die('could not fork');
}
elseif($pid)
{
  $m_in = uniqid();
  $m_out = $client->task('test_echo', $m_in);
  $t->is($m_out, $m_in, '->task() scalar workload');

  $m_in = array('not' => uniqid());
  $m_out = $client->task('test_echo', $m_in);
  $t->is_deeply($m_out, $m_in, '->task() array workload');

  $m_in = new stdClass; $m_in->var = uniqid();
  $m_out = $client->task('test_echo', $m_in);
  $t->ok($m_in == $m_out and $m_in->var === $m_out->var, '->task() object workload');

  try
  {
    $client->task('test_fail');
    $t->fail('->task() raises sfGearmanException if failing task');
  }
  catch(sfGearmanException $e)
  {
    $t->pass('->task() raises sfGearmanException if failing task');
  }

  $m_in = uniqid();
  $m_out = $client->task('test_data', $m_in);
  $t->is($m_out, $m_in, '->task() data from worker do not alter result');

  $m_in = uniqid();
  $m_out = $client->task('test_status', $m_in);
  $t->is($m_out, $m_in, '->task() status from worker do not alter result');

  $m_in = uniqid();
  $m_out = $client->task('test_warning', $m_in);
  $t->is($m_out, $m_in, '->task() warning from worker do not alter result');

  pcntl_wait($status);
}
else
{
  $n = 7;
  $t->skip('child worker test', $n);
  $worker->loop($n);
  exit(0);
}



