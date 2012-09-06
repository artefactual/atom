<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The logger interface
 *
 * @package sfSearch
 * @subpackage Log
 * @author Carl Vondrick
 */
interface xfLogger
{
  /**
   * Logs a message
   *
   * @param string $message
   */
  public function log($message, $section = 'sfSearch');
}
