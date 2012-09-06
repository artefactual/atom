<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'result/xfRetortFilterCallback.interface.php';

class xfMockRetortFilterCallback implements xfRetortFilterCallback
{
  public function filter($response, xfDocumentHit $hit, $method, array $args = array())
  {
    return md5($response);
  }
}
