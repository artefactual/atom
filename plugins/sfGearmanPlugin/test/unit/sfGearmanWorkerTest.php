<?php

/**
 * sfGearmanWorker tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(11, new lime_output_color());

$t->diag('sfGearmanWorker');
$client = new sfGearmanClient;
$worker = new sfGearmanWorker;

$t->ok($worker instanceof GearmanWorker, 'sfGearmanWorker extends GearmanWorker');
$t->ok($worker->options() & GEARMAN_WORKER_GRAB_UNIQ, 'sfGearmanWorkers grab uniques');

$dispatcher = $configuration->getEventDispatcher();

$dispatcher->connect('gearman.add_function', function (sfEvent $e) {
  global $t;
  $t->pass('::addFunction() notify a gearman.add_function sfEvent');
  if ($e['function'] == 'size') {
    $t->pass('::addFunction() event has function name');
  }
});

$dispatcher->connect('gearman.start', function (sfEvent $e) {
  global $t;
  $t->pass('::loop() notify a gearman.start sfEvent');
});

$dispatcher->connect('gearman.stop', function (sfEvent $e) {
  global $t;
  $t->pass('::loop() notify a gearman.stop sfEvent');
});

$dispatcher->connect('gearman.timeout', function (sfEvent $e) {
  global $t;
  $t->pass('::loop() notify a gearman.timeout sfEvent');
});

$dispatcher->connect('gearman.job', function (sfEvent $e) {
  global $t;

  $t->pass('::handler() notify a gearman.job sfEvent');
  if ($e['job']) {
    $t->pass('::handler() has job as parameter');
  }
});

$worker = new sfGearmanWorker(array('config' => 'test'), $dispatcher);

try
{
  $worker->loop(null, 1000);
  $t->fail('::loop() raises a sfGearmanTimeoutException');
}
catch(sfGearmanTimeoutException $e)
{
  $t->pass('::loop() raises a sfGearmanTimeoutException');
}

$pid = pcntl_fork();
if ($pid == -1)
{
  die('could not fork');
}
elseif($pid)
{
  $t->skip('child client test', 1);

  $worker->loop(1);

  pcntl_wait($status);
}
else
{
  $t->skip('child client test', 4);

  $s = $client->task('md5', __FILE__);
  $t->is($s, md5_file(__FILE__), '::work() did his job');

  exit(0);
}


