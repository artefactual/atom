<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A special filter for the xfRetortFilterer retort.
 *
 * @package sfSearch
 * @subpackage Result
 * @author Carl Vondrick
 */
interface xfRetortFilterCallback
{
  /**
   * Filters the response.
   *
   * @param string $response
   * @param xfDocumentHit $hit
   * @param string $method
   * @param array $args
   * @returns string
   */
  public function filter($response, xfDocumentHit $hit, $method, array $args = array());
}
