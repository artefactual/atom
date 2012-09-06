<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An application configuration for xfSearch.  This will be hopefully removed.
 *
 * This is a hack and will be hopefully removed.
 * See: http://groups.google.com/group/symfony-devs/browse_thread/thread/dc399312da49598a
 *
 * @package sfSearch
 * @subpackage Utilities
 * @author Carl Vondrick
 */
class xfSearchConfiguration extends sfApplicationConfiguration
{
  public function configure()
  {
    sfConfig::set('sf_test', true);
  }
}
