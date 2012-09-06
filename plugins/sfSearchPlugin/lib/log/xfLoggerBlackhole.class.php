<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A blackhole logger that does not log.
 *
 * @package sfSearch
 * @subpackage Log
 * @author Carl Vondrick
 */
final class xfLoggerBlackhole implements xfLogger
{
  /**
   * @see xfLogger
   */
  public function log($message, $section = 'sfSearch')
  {
  }
}
