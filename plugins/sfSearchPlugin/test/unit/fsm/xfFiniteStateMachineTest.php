<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'fsm/xfFiniteStateMachine.class.php';
require 'fsm/xfFiniteStateMachineAction.interface.php';
require 'util/xfException.class.php';

$t = new lime_test(25, new lime_output_color);

class CounterAction implements xfFiniteStateMachineAction
{
  public $counter = 0;

  public function execute()
  {
    $this->counter++;
  }
}

$fsm = new xfFiniteStateMachine(array('on', 'off', 'burned out', 'broken'));
$fsm->setInitialState('off');
$fsm->addTransitions(array(
  array('on',         'push',       'off'),
  array('off',        'push',       'on'),
  array('on',         'smash',      'broken'),
  array('off',        'smash',      'broken'),
  array('off',        'wait',       'off'),
  array('broken',     'replace',    'off'),
  array('broken',     'wait',       'broken'),
  array('burned out', 'replace',    'off'),
  array('on',         'short out',  'burned out')
));

$t->diag('->addTransitions(), ->addTransition()');
try {
  $msg = '->addTransitions() fails if array is not a two dimensional array with 3 items in the 2nd dimension';
  $fsm->addTransitions(array('foo'));
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

try {
  $msg = '->addTransition() fails if the source state does not exist.';
  $fsm->addTransition('exploded', 'implode', 'on');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

try {
  $msg = '->addTransition() fails if the target source does not exist.';
  $fsm->addTransition('off', 'explode', 'exploded');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

try {
  $msg = '->addTransition() fails if the transition already exists.';
  $fsm->addTransition('on', 'push', 'broken');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->setState()');
$fsm->setState('on');
$t->is($fsm->getState(), 'on', '->setState() changes the state');
$fsm->setState(null);
$t->is($fsm->getState(), 'off', '->setState() to null sets the initial state');

try {
  $msg = '->setState() fails if the state does not exist.';
  $fsm->setState('exploded');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$t->diag('->process()');
$t->is($fsm->getState(), 'off', '->getState() returns the inital state');
$t->is($fsm->process('push')->getState(), 'on', '->process() changes the state according to the transitions');
$t->is($fsm->process('smash')->getState(), 'broken', '->process() changes the state according to the transitions');
$t->is($fsm->processMany(array('replace', 'push', 'short out'))->getState(), 'burned out', '->processMany() processes multiple states');

try {
  $msg = '->process() fails when there is no transition defined for an input';
  $fsm->process('twist');
  $t->fail($msg);
} catch (Exception $e) {
  $t->pass($msg);
}

$fsm->setDefaultTransition('broken');
$t->is($fsm->process('twist')->getState(), 'broken', '->process() uses the default transition if possible');
$fsm->setDefaultTransition(null);

$fsm->reset();
$t->is($fsm->getState(), 'off', '->reset() resets the state');

$t->diag('exit actions');
$fsm->reset();
$exit = new CounterAction;
$fsm->addExitAction('off', $exit);
$fsm->process('push');
$t->is($exit->counter, 1, '->process() calls an exit action when leaving a state');
$fsm->process('push');
$t->is($exit->counter, 1, '->process() does not call an exit action when not leaving the state');
$fsm->process('wait');
$t->is($exit->counter, 1, '->process() does not call an exit action when the state does not change');

$t->diag('entry actions');
$fsm->reset();
$enter = new CounterAction;
$fsm->addEntryAction('broken', $enter);
$fsm->process('smash');
$t->is($enter->counter, 1, '->process() calls an entry action when entering the state');
$fsm->process('wait');
$t->is($enter->counter, 1, '->process() does not call an entry action when the state does not change');
$fsm->process('replace');
$t->is($enter->counter, 1, '->process() does not call an entry action when leaving the state');

$t->diag('transition actions');
$fsm->reset();
$transition = new CounterAction;
$fsm->addTransitionAction('off', 'on', $transition);
$fsm->process('push');
$t->is($transition->counter, 1, '->process() calls transition actions when matching');
$fsm->process('push');
$t->is($transition->counter, 1, '->process() does not call transition actions when they do not match');

$t->diag('input actions');
$fsm->reset();
$input = new CounterAction;
$fsm->addInputAction('off', 'push', $input);
$fsm->process('push');
$t->is($input->counter, 1, '->process() calls input actions when matching');
$fsm->reset();
$fsm->process('smash');
$t->is($input->counter, 1, '->process() does not call input actions when they do not match');
$fsm->setState('on');
$fsm->process('push');
$t->is($input->counter, 1, '->process() does not call input actions when they do not match');
