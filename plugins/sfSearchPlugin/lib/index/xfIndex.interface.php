<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The index interface defines a common interface for all indices
 *
 * @package sfSearch
 * @subpackage Index
 * @author Carl Vondrick
 */
interface xfIndex
{
  /**
   * Sets the index name.
   *
   * @param string $name
   */
  public function setName($name);

  /**
   * Gets hte index name.
   *
   * @returns string
   */
  public function getName();

  /**
   * Sets the logger
   *
   * @param xfLogger $logger
   */
  public function setLogger(xfLogger $logger);

  /**
   * Gets the logger
   *
   * @returns xfLogger
   */
  public function getLogger();

  /**
   * Sets the service registry
   *
   * @param xfServiceRegistry $registry
   */
  public function setServiceRegistry(xfServiceRegistry $registry);

  /**
   * Gets the service registry
   *
   * @returns xfServiceRegistry
   */
  public function getServiceRegistry();

  /**
   * Inserts an input into the index
   *
   * @param mixed $input
   */
  public function insert($input);

  /**
   * Removes an input from the index
   *
   * @param mixed $input
   */
  public function remove($input);

  /**
   * Empties and populates the index
   */
  public function populate();

  /**
   * Optimizes the index
   */
  public function optimize();

  /**
   * Searches the index
   *
   * @param xfCriterion $crit
   */
  public function find(xfCriterion $crit);

  /**
   * Describes the index with any information worth noting
   *
   * @returns array
   */
  public function describe();
}
