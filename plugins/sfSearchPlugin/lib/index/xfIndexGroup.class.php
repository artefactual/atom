<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The index group collects multiple indices with a common service registry.
 *
 * @package sfSearch
 * @subpackage Index
 * @author Carl Vondrick
 */
abstract class xfIndexGroup extends xfIndexCommon
{
  /**
   * The indices this group holds
   *
   * @var array
   */
  private $indices = array();

  /**
   * Adds an index
   *
   * @param string $name The index name
   * @param xfIndex $index The index to add
   */
  final public function addIndex($name, xfIndex $index)
  {
    $this->indices[$name] = $index;
  }

  /**
   * Gets an index
   *
   * @param string $name The index name
   */
  final public function getIndex($name)
  {
    $this->setup();

    if (!isset($this->indices[$name]))
    {
      throw new xfException('Index "' . $name . '" could not be found.');
    }

    return $this->indices[$name];
  }

  /**
   * @see xfIndexCommon
   */
  final protected function postSetup()
  {
    // configure all indices
    foreach ($this->indices as $index)
    {
      if (!$index->isSetup())
      {
        $index->setServiceRegistry($this->getServiceRegistry());
      }

      $index->setLogger($this->getLogger());
    }

    parent::postSetup();
  }

  /**
   * @see xfIndex
   */
  final public function insert($object)
  {
    $this->setup();

    foreach ($this->indices as $index)
    {
      $index->insert($object);
    }
  }

  /**
   * @see xfIndex
   */
  final public function remove($object)
  {
    $this->setup();

    foreach ($this->indices as $index)
    {
      $index->remove($object);
    }
  }

  /**
   * @see xfIndex
   */
  final public function populate()
  {
    $this->setup();

    $start = microtime(true);

    $this->getLogger()->log('Populating group...', $this->getName());

    foreach ($this->indices as $name => $index)
    {
      $this->getLogger()->log('Populating index "' . $name . '"...', $this->getName());

      $index->populate();
    }

    $this->getLogger()->log('Group populated in "' . round(microtime(true) - $start, 2) . '" seconds.', $this->getName());
  }

  /**
   * @see xfIndex
   */
  final public function optimize()
  {
    $this->setup();

    $start = microtime(true);

    $this->getLogger()->log('Optimizing group...', $this->getName());

    foreach ($this->indices as $name => $index)
    {
      $this->getLogger()->log('Optimizing index "' . $name . '"...', $this->getName());

      $index->optimize();
    }

    $this->getLogger()->log('Group optimized in "' . round(microtime(true) - $start, 2) . '" seconds.', $this->getName());
  }

  /**
   * @see xfIndex
   */
  final public function describe()
  {
    $this->setup();

    $response = array();

    foreach ($this->indices as $name => $index)
    {
      $response[$index->getName() . ' (' . $name . ')'] = $index->describe();
    }

    return $response;
  }

  /**
   * @see xfIndex
   * @todo Implement xfResultIteratorMultiple
   * @throws xfException no matter what
   */
  final public function find(xfCriterion $crit)
  {
    throw new xfException('You cannot search on a group at this time.');
  }
}

