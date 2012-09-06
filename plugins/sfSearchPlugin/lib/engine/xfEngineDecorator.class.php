<?php
/**
 * This file is part of the sfCachedSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A engine that decorates another engine.
 *
 * @package sfSearch
 * @subpackage Engine
 * @author Carl Vondrick
 */
abstract class xfEngineDecorator implements xfEngine
{
  /**
   * The wrapped engine
   *
   * @var xfEngine
   */
  protected $engine;

  /**
   * Constructor to set engine.
   *
   * @param xfEngine $engine
   */
  public function __construct(xfEngine $engine)
  {
    $this->engine = $engine;
  }

  /**
   * Gets the engine
   *
   * @returns xfEngine
   */
  public function getEngine()
  {
    return $this->engine;
  }

  /**
   * @see xfEngine
   */
  public function open()
  {
    $this->engine->open();
  }

  /**
   * @see xfEngine
   */
  public function close()
  {
    $this->engine->close();
  }

  /**
   * @see xfEngine
   */
  public function find(xfCriterion $query)
  {
    return $this->engine->find($query);
  }

  /**
   * @see xfEngine
   */
  public function findGuid($guid)
  {
    return $this->engine->findGuid($guid);
  }

  /**
   * @see xfEngine
   */
  public function delete($guid)
  {
    return $this->engine->delete($guid);
  }

  /**
   * @see xfEngine
   */
  public function add(xfDocument $doc)
  {
    $this->engine->add($doc);
  }

  /**
   * @see xfEngine
   */
  public function erase()
  {
    $this->engine->erase();
  }

  /**
   * @see xfEngine
   */
  public function count()
  {
    return $this->engine->count();
  }

  /**
   * @see xfEngine
   */
  public function optimize()
  {
    $this->engine->optimize();
  }

  /**
   * @see xfEngine
   */
  public function describe()
  {
    return $this->engine->describe();
  }

  /**
   * @see xfEngine
   */
  public function id()
  {
    return $this->engine->id();
  }
}
