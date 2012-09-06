<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An interface for an index.
 *
 * The constructor *should not* open a connection to the index, if it needs to
 * be opened.  If nothing needs to be opened, ->open() and ->close() should do
 * nothing.
 *
 * @package xfSearch
 * @subpackage Engine
 * @author Carl Vondrick
 */
interface xfEngine
{
  /**
   * Opens a connection.
   */
  public function open();

  /**
   * Closes the connection.
   */
  public function close();

  /**
   * Searches the index.
   *
   * @param xfCriterion $query The query
   * @returns array or SPL Iterator + SPL Countable compatible instance
   */
  public function find(xfCriterion $query);

  /**
   * Retrieves a document from a guid
   *
   * @param string $guid The guid
   * @returns xfDocument
   * @throws xfEngineException if guid is not found
   */
  public function findGuid($guid);

  /**
   * Deletes a GUID
   *
   * @param string $guid The guid
   * @returns int Number of documents deleted
   */
  public function delete($guid);

  /**
   * Adds a document to the index.
   *
   * @param xfDocument $doc The document
   */
  public function add(xfDocument $doc);

  /**
   * Erases the index.
   *
   * WARNING: This cannot be undone. Please backup your index before executing
   * this method. 
   */
  public function erase();

  /**
   * Returns the number of documents in the index.
   *
   * @returns int The number of documents
   */
  public function count();

  /**
   * Runs the index optimize routine.
   *
   * If engine does not support optimizing, then the method should do nothing.
   */
  public function optimize();

  /**
   * Describes the index with any statistics worth giving.
   *
   * @returns array
   */
  public function describe();

  /**
   * Gets a unique ID for this engine.
   *
   * @returns string
   */
  public function id();
}
