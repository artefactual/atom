<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A Finite State Machine (FSM)
 *
 * A FSM has a state.  The state can represent anything. The common example
 * is a light bulb being on or off.  Both "on" and "off" are states.
 *
 * When we provide the FSM with an input, it looks up a transition that can 
 * handle the change.  A transition consists of a source state, an input, and 
 * a target state. The FSM determines the new state from the target state by
 * matching the current state with the source state and the input with the
 * transition input. Once a transition is found, the state is changed.
 *
 * The FSM can be configured to execute different actions depending on the
 * current context.  Four hooks are available:
 *
 *  Exit Actions        Execute when leaving a state, eg exit actions
 *                      registered for "on" will run when going from "on"
 *                      to "off"
 *
 *  Entry Actions       Execute when entering a state, eg entry actions
 *                      registered for "off" will run when going from "on"
 *                      to "off"
 *
 *  Transition Actions  Execute when a transition happens, eg going from "on"
 *                      to "off", no matter the input.
 *
 *  Input Actions       The same as transition actions, except they are aware
 *                      of the input.  Input actions allow actions to execute
 *                      when the state changes from "on" to "off" when the
 *                      input is "power failure", but will not execute when
 *                      the input is "pushed down."  
 *
 * This FSM is deterministic: given a state and input, the next step can be 
 * determined.
 *
 * Based on implementations from Zend_Search_Lucene and PEAR::FSM
 *
 * @package sfSearch
 * @subpackage FSM
 * @author Carl Vondrick
 */
final class xfFiniteStateMachine
{
  /**
   * Possible states. 
   *
   * This indicates the finite states that this machine can be in.
   *
   * @var array
   */
  private $states = array();

  /**
   * The current state of the machine.
   *
   * @var scalar
   */
  private $currentState;

  /**
   * The initial state.
   *
   * @var scalar
   */
  private $initialState;

  /**
   * Transitions to move between states depending on inputs.
   *
   * $this->transitions[0] = array($sourceState, $input, $targetState);
   *
   * @var array
   */
  private $transitions = array();

  /**
   * The default transition to execute if no transition can be found.
   *
   * @var scalar
   */
  private $defaultTransition = null;

  /**
   * Actions to execute on entry (only when state changes).
   *
   * Entry actions are executed when a state begins reign.  It depends only on
   * the state and NOT the input.
   *
   * @var array
   */
  private $entryActions = array();

  /**
   * Actions to execute on exit (only when state changes).
   *
   * Exit actions are executed when a state ends reign. It depends only on
   * the state and NOT the input.
   *
   * @var array
   */
  private $exitActions = array();

  /**
   * Actions to execute on transition (even if state does not change)
   *
   * Transition actions are executed when a transition happens, even if the 
   * state does not change.  It depends only on the source and target state, 
   * and does NOT depend on the input.
   *
   * @var array
   */
  private $transitionActions = array();

  /**
   * Input actions to execute depending on the input.
   *
   * Input actions are the same as transition actions, but they also depend on 
   * the input.
   *
   * @var array
   */
  private $inputActions = array();

  /**
   * Constructor to set states.
   *
   * @param array $states
   */
  public function __construct(array $states = array())
  {
    $this->addStates($states);
  }

  /**
   * Adds multiple states
   *
   * @param array $states
   */
  public function addStates(array $states)
  {
    foreach ($states as $state)
    {
      $this->addState($state);
    }
  }

  /**
   * Adds a state.
   *
   * @param scalar $state The state to add
   */
  public function addState($state)
  {
    $this->states[$state] = $state;

    if ($this->initialState === null)
    {
      $this->initialState = $state;
    }
  }

  /**
   * Sets the current state.
   *
   * @param scalar $state
   */
  public function setState($state)
  {
    if ($state !== null)
    {
      $this->checkState($state);
    }

    $this->currentState = $state;
  }

  /**
   * Gets the current state.
   */
  public function getState()
  {
    if (!$this->currentState)
    {
      $this->currentState = $this->initialState;
    }

    return $this->currentState;
  }


  /**
   * Checks that a state exists and throws exception if not.
   *
   * @param scalar $state
   */
  private function checkState($state)
  {
    if (!isset($this->states[$state]))
    {
      throw new xfException('State "' . $state . '" does not exist.');
    }
  }

  /**
   * Sets the initial state that will be set by default.
   *
   * @param scalar $state
   */
  public function setInitialState($state)
  {
    $this->checkState($state);

    $this->initialState = $state;
  }

  /**
   * Adds multiple transitions
   *
   * @param array $transitions
   */
  public function addTransitions(array $transitions)
  {
    foreach ($transitions as $transition)
    {
      if (!is_array($transition) || count($transition) != 3)
      {
        throw new xfException('addTransitions() must take a multidimensional array of n*3');
      }

      $this->addTransition($transition[0], $transition[1], $transition[2]);
    }
  }

  /**
   * Adds a transition
   *
   * @param scalar $sourceState The original state
   * @param scalar $input The input that must happen for this state
   * @param scalar $targetState The new state if the transition succeeds
   */
  public function addTransition($sourceState, $input, $targetState)
  {
    $this->checkState($sourceState);
    $this->checkState($targetState);
    
    if (isset($this->transitions[$sourceState][$input]))
    {
      throw new xfException('A transition for source state "' . $sourceState . '" and input "' . $input . '" already exists.');
    }

    if (!isset($this->transitions[$sourceState]))
    {
      $this->transitions[$sourceState] = array();
    }
    if (!isset($this->transitions[$sourceState][$input]))
    {
      $this->transitions[$sourceState][$input] = array();
    }

    $this->transitions[$sourceState][$input] = $targetState;
  }

  /**
   * Adds a default transition if one cannot be found.
   *
   * @param scalar $target
   */
  public function setDefaultTransition($target)
  {
    $this->defaultTransition = $target;
  }

  /**
   * Adds an action when leaving a state.
   *
   * @param scalar $state
   * @param xfFiniteStateMachineAction $action
   */
  public function addExitAction($state, xfFiniteStateMachineAction $action)
  {
    $this->checkState($state);

    if (!isset($this->exitActions[$state]))
    {
      $this->exitActions[$state] = array();
    }

    $this->exitActions[$state][] = $action;
  }

  /**
   * Adds an action when entering a state.
   *
   * @param scalar $state
   * @param xfFiniteStateMachineAction $action
   */
  public function addEntryAction($state, xfFiniteStateMachineAction $action)
  {
    $this->checkState($state);

    if (!isset($this->entryActions[$state]))
    {
      $this->entryActions[$state] = array();
    }

    $this->entryActions[$state][] = $action;

  }

  /**
   * Adds an action when transiting from states.
   *
   * @param scalar $source
   * @param scalar $target
   * @param xfFiniteStateMachineAction $action
   */
  public function addTransitionAction($source, $target, xfFiniteStateMachineAction $action)
  {
    $this->checkState($source);
    $this->checkState($target);

    if (!isset($this->transitionActions[$source]))
    {
      $this->transitionActions[$source] = array();
    }
    if (!isset($this->transitionActions[$source][$target]))
    {
      $this->transitionActions[$source][$target] = array();
    }

    $this->transitionActions[$source][$target][] = $action;
  }

  /**
   * Adds an action when transitioning from state depending on input.
   *
   * @param scalar $source
   * @param scalar $input
   * @param xfFiniteStateMachineAction $action
   */
  public function addInputAction($source, $input, xfFiniteStateMachineAction $action)
  {
    $this->checkState($source);

    if (!isset($this->inputActions[$source]))
    {
      $this->inputActions[$source] = array();
    }
    if (!isset($this->inputActions[$source][$input]))
    {
      $this->inputActions[$source][$input] = array();
    }

    $this->inputActions[$source][$input][] = $action;
  }

  /**
   * Gets a transition target.
   *
   * @param scalar $input The input 
   */
  public function getTransitionTarget($input)
  {
    if (!isset($this->transitions[$this->currentState][$input]))
    {
      if ($this->defaultTransition === null)
      {
        throw new xfException('No transition defined for input "' . $input . '" when at state "' . $this->currentState . '" and no default transition defined.');
      }

      return $this->defaultTransition;
    }

    return $this->transitions[$this->currentState][$input];
  }

  /**
   * Processes many inputs in order.
   *
   * @param array $inputs
   */
  public function processMany(array $inputs)
  {
    foreach ($inputs as $input)
    {
      $this->process($input);
    }

    return $this;
  }

  /**
   * Processes an input and transitions states accordings to the defined rules.
   *
   * @param scalar $input The input to process
   */
  public function process($input)
  {
    $current = $this->getState();
    $target = $this->getTransitionTarget($input);

    // execute exit actions on the current state, if the state is changing
    if ($current !== $target)
    {
      $this->executeExitActions($current);
    }

    // execute the input actions from $current to $target with $input
    $this->executeInputActions($current, $input);

    // change the state
    $this->setState($target);
    
    // execute the transition actions from $current to $target
    $this->executeTransitionActions($current, $target);

    // execute entry actions on target state, if state is changing
    if ($current !== $target)
    {
      $this->executeEntryActions($target);
    }

    return $this;
  }

  /**
   * Executes the exit actions for a state.
   *
   * @param scalar $state
   */
  private function executeExitActions($state)
  {
    if (isset($this->exitActions[$state]))
    {
      foreach ($this->exitActions[$state] as $action)
      {
        $action->execute();
      }
    }
  }

  /**
   * Executes the input actions for a state.
   *
   * @param scalar $state
   * @param scalar $input
   */
  private function executeInputActions($state, $input)
  {
    if (isset($this->inputActions[$state][$input]))
    {
      foreach ($this->inputActions[$state][$input] as $action)
      {
        $action->execute();
      }
    }
  }

  /**
   * Executes the transition actions for a state.
   *
   * @param scalar $current
   * @param scalar $target
   */
  private function executeTransitionActions($current, $target)
  {
    if (isset($this->transitionActions[$current][$target]))
    {
      foreach ($this->transitionActions[$current][$target] as $action)
      {
        $action->execute();
      }
    }
  }
  
  /**
   * Executes the entry actions for a state.
   *
   * @param scalar $state
   */
  private function executeEntryActions($state)
  {
    if (isset($this->entryActions[$state]))
    {
      foreach ($this->entryActions[$state] as $action)
      {
        $action->execute();
      }
    }
  }

  /**
   * Resets the finite state machine.
   */
  public function reset()
  {
    $this->setState(null);
  }
}
