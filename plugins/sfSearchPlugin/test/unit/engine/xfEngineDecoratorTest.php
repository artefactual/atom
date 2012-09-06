<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require dirname(__FILE__) . '/../../bootstrap/unit.php';
require 'engine/xfEngine.interface.php';
require 'engine/xfEngineDecorator.class.php';
require 'criteria/xfCriterion.interface.php';
require 'criteria/xfCriterionTerm.class.php';
require 'document/xfDocument.class.php';

class EngineDec extends xfEngineDecorator
{
}

class EngineConc implements xfEngine
{
  private $lime;
  
  public function __construct(lime_test $lime)
  {
    $this->lime = $lime;
  }

  public function open()
  {
    $this->lime->pass('->open() is called');
  }

  public function close()
  {
    $this->lime->pass('->close() is called');
  }

  public function find(xfCriterion $query)
  {
    $this->lime->pass('->find() is called');
  }

  public function findGuid($guid)
  {
    $this->lime->pass('->findGuid() is called');
  }

  public function delete($guid)
  {
    $this->lime->pass('->delete() is called');
  }

  public function add(xfDocument $doc)
  {
    $this->lime->pass('->add() is called');
  }

  public function erase()
  {
    $this->lime->pass('->erase() is called');
  }

  public function count()
  {
    $this->lime->pass('->count() is called');
  }

  public function optimize()
  {
    $this->lime->pass('->optimize() is called');
  }

  public function describe()
  {
    $this->lime->pass('->describe() is called');
  }

  public function id()
  {
    $this->lime->pass('->id() is called');
  }
}

$t = new lime_test(11, new lime_output_color);
$concrete = new EngineConc($t);
$decorator = new EngineDec($concrete);

$t->is($decorator->getEngine(), $concrete, '->getEngine() returns the real engine');
$decorator->open();
$decorator->close();
$decorator->find(new xfCriterionTerm('foo'));
$decorator->delete('foo');
$decorator->add(new xfDocument('foo'));
$decorator->erase();
$decorator->count();
$decorator->optimize();
$decorator->describe();
$decorator->id();
