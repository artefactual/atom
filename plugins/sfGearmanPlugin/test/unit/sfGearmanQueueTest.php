<?php

/**
 * sfGearmanQueue tests.
 */
include dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(16, new lime_output_color());

$t->diag('sfGearmanQueue');
$msg = "a message";
$r = sfGearmanQueue::put('test', $msg);
$t->ok($r, '::put() scalar message');
$t->is(sfGearmanQueue::get('test'), $msg, '::get() scalar message');

$msg = array('not' => 'scalar');
$r = sfGearmanQueue::put('test', $msg);
$t->ok($r, '::put() not scalar message');
$t->is_deeply(sfGearmanQueue::get('test'), $msg, '::get() not scalar message');

try
{
  sfGearmanQueue::get('test', 100);
  $t->fail('get from an empty queue w/ timeout throws an exception');
}
catch(sfGearmanTimeoutException $e)
{
  $t->pass('get from an empty queue w/ timeout throws an exception');
}

$t->diag('different queues can be ::put/::get in any order');
$msg1 = "a message 1";
$msg2 = "a message 2";
$r1 = sfGearmanQueue::put('test1', $msg1);
$t->ok($r1, '::put() queue #1');
$r2 = sfGearmanQueue::put('test2', $msg2);
$t->ok($r2, '::put() queue #2');
$t->is(sfGearmanQueue::get('test2'), $msg2, '::get() queue #2');
$t->is(sfGearmanQueue::get('test1'), $msg1, '::get() queue #1');

$t->diag('messages priority alter ::get order');
$msgl = "a message low";
$msgn = "a message normal";
$msgh = "a message high";
$rl = sfGearmanQueue::put('test', $msgl, sfGearman::LOW);
$t->ok($rl, '::put() low');
$rn = sfGearmanQueue::put('test', $msgn);
$t->ok($rn, '::put() normal');
$rh = sfGearmanQueue::put('test', $msgh, sfGearman::HIGH);
$t->ok($rh, '::put() high');
$t->is(sfGearmanQueue::get('test'), $msgh, '::get() high');
$t->is(sfGearmanQueue::get('test'), $msgn, '::get() normal');
$t->is(sfGearmanQueue::get('test'), $msgl, '::get() low');

$pid = pcntl_fork();
if ($pid == -1)
{
  die('could not fork');
}
elseif($pid)
{
  $t->is(sfGearmanQueue::get('test'), $msg, '::get() blocks before ::put()');

  pcntl_wait($status);
}
else
{
  sleep(1);
  $t->skip('child worker test', 1);
  sfGearmanQueue::put('test', $msg);
  exit(0);
}



