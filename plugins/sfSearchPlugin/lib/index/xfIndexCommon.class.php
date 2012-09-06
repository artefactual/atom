<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A common helper class for xfIndexSingle and xfIndexGroup
 *
 * @package sfSearch
 * @subpackage Index
 * @author Carl Vondrick
 */
abstract class xfIndexCommon implements xfIndex
{
  /**
   * The logger
   *
   * @var xfLogger
   */
  private $logger;

  /**
   * The service registry.
   *
   * @var xfServiceRegistry
   */
  private $registry;

  /**
   * The name of this index.
   *
   * @var string
   */
  private $name;

  /**
   * True if index is setup
   *
   * @var bool
   */
  private $setup = false;

  /**
   * @see xfIndex
   */
  public function __construct()
  {
    $this->registry = new xfServiceRegistry;
    $this->name = get_class($this);
    
    $this->setLogger(new xfLoggerBlackhole);

    $this->initialize();
  }

  /**
   * Sets the index name.
   *
   * @param string $name
   */
  final public function setName($name)
  {
    $this->name = $name;
  }

  /**
   * Gets the index name.
   *
   * @returns string
   */
  final public function getName()
  {
    return $this->name;
  }

  /**
   * @see xfIndex
   */
  final public function setLogger(xfLogger $logger)
  {
    $this->logger = $logger;
  }

  /**
   * @see xfIndex
   */
  final public function getLogger()
  {
    return $this->logger;
  }

  /**
   * Sets the service registry.
   *
   * @param xfServiceRegistry $registry
   */
  final public function setServiceRegistry(xfServiceRegistry $registry)
  {
    $this->registry = $registry;
  }

  /**
   * Gets the service registry.
   *
   * @returns xfServiceRegistry
   */
  final public function getServiceRegistry()
  {
    return $this->registry;
  }

  /**
   * Runs the setup routine to make sure the index is in a workable state.
   */
  final protected function setup()
  {
    if (!$this->setup)
    {
      $this->configure();

      $this->postSetup();

      $this->setup = true;
    }
  }

  /**
   * A routine that is executed after setting up.
   */
  protected function postSetup()
  {
    // nothing to do
  }

  /**
   * Returns true if index is in a workable state
   *
   * @returns bool 
   */
  final public function isSetup()
  {
    return $this->setup;
  }

  /**
   * Runs the internal setup procedure.
   *
   * This method should be overloaded by search indexes.  This method should
   * initialize the service registry and setup the backend engine.
   */
  protected function configure()
  {
    // nothing to do
  }

  /**
   * Runs the initial setup procedure to configure meta information, such as a
   * name.
   */
  protected function initialize()
  {
    // nothing to do
  }
}
