<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Identifiers provide the meta data to a service and builder.
 *
 * @package sfSearch
 * @subpackage Builder
 * @author Carl Vondrick
 */
interface xfIdentifier
{
  /**
   * Constant for ->match().  Indicates that can identify.
   */
  const MATCH_YES = 1;

  /**
   * Constant for ->match().  Indicates that can identify, but should ignore.
   */
  const MATCH_IGNORED = 2;

  /**
   * Constant for ->match().  Indicates that cannot identify.
   */
  const MATCH_NO = 3;

  /**
   * Gets the name for this service.
   *
   * @returns string
   */
  public function getName();

  /**
   * Calculates a GUID for an object.
   *
   * @param mixed $input
   * @returns string
   */
  public function getGuid($input);

  /**
   * Tests to see if the identifier can identify it.
   *
   * @param mixed $input
   * @returns int a MATCH_* constant
   */
  public function match($input);

  /**
   * Discovers all objects that this identifier can match.
   *
   * @param int $count The count to allow a method to page through long results.
   * @returns array of results
   */
  public function discover($count);
}
