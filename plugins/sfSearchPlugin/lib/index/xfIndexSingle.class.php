<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An index that manages services and controls indexing.
 *
 * @package sfSearch
 * @subpackage Index
 * @author Carl Vondrick
 */
abstract class xfIndexSingle extends xfIndexCommon
{
  /**
   * The backend search engine.
   
   * @var xfEngine
   */
  private $engine = null;

  /**
   * Sets the engine index.
   *
   * @param xfEngine $index
   */
  final public function setEngine(xfEngine $engine)
  {
    $this->engine = $engine;
  }

  /**
   * Gets the engine.
   *
   * @returns xfEngine
   */
  final public function getEngine()
  {
    return $this->engine;
  }

  /**
   * @see xfIndexCommon
   */
  final protected function postSetup()
  {
    if (!$this->engine || !($this->engine instanceof xfEngine))
    {
      throw new xfException('Index "' . $this->getName() . '" must have an engine');
    }
    
    parent::postSetup();
  }

  /**
   * @see xfIndex
   */
  final public function insert($input)
  {
    $this->setup();

    $this->engine->open();

    try
    {
      $doc = $this->getServiceRegistry()->locate($input)->buildDocument($input);
      $this->engine->add($doc);
      $this->getLogger()->log('Inserted document "' . $doc->getGuid() . '" into the index', $this->getName());
    }
    catch (xfServiceIgnoredException $e)
    {
    }
  }

  /**
   * @see xfIndex
   */
  final public function remove($input)
  {
    $this->setup();

    $this->engine->open();

    try
    {
      $guid = $this->getServiceRegistry()->locate($input)->getIdentifier()->getGuid($input);
      $this->engine->delete($guid);
      $this->getLogger()->log('Removed document "' . $guid . '" from the index', $this->getName());
    }
    catch (xfServiceIgnoredException $e)
    {
    }
  }

  /**
   * @see xfIndex
   */
  public function populate()
  {
    $this->setup();

    $start = microtime(true);
    $this->getLogger()->log('Populating index...', $this->getName());
    $this->engine->erase();
    $this->getLogger()->log('Index erased.', $this->getName());

    $this->engine->open();

    $services = $this->getServiceRegistry()->getServices();

    $this->getLogger()->log('Found "' . count($services) . '" services.', $this->getName());
   
    foreach ($services as $service)
    {
      $name = $service->getIdentifier()->getName();

      $this->getLogger()->log('Processing service "' . $name . '"...', $this->getName());

      for ($x = 0; count($objects = $service->getIdentifier()->discover($x)) > 0; $x++)
      {
        foreach ($objects as $object)
        {
          $doc = $service->buildDocument($object);
          $this->engine->add($doc);
          $this->getLogger()->log('Document "' . $doc->getGuid() . '" inserted.', $this->getName());
        }
      }
    }

    $this->getLogger()->log('Index populated in "' . round(microtime(true) - $start, 2) . '" seconds.', $this->getName());
  }

  /**
   * @see xfIndex
   */
  public function optimize()
  {
    $this->setup();

    $start = microtime(true);
    $this->engine->open();
    $this->getLogger()->log('Optimizing index...', $this->getName());
    $this->engine->optimize();
    $this->getLogger()->log('Index optimized in "' . round(microtime(true) - $start, 2) . '" seconds.', $this->getName());
  }

  /**
   * @see xfIndex
   */
  final public function find(xfCriterion $crit)
  {
    $this->setup();

    $this->engine->open();

    return new xfResultIterator($this->engine->find($crit), $this->getServiceRegistry());
  }

  /**
   * @see xfIndex
   */
  final public function describe()
  {
    $this->setup();

    return $this->engine->describe();
  }
}
