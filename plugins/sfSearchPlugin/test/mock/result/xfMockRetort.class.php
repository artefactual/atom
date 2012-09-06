<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once 'result/xfRetort.interface.php';
require_once 'result/xfDocumentHit.class.php';

/**
 * A mock retort.
 *
 * @pacakge xfSearch
 * @subpackage Mock
 */
class xfMockRetort implements xfRetort
{
  public $can = true, $response = 42;

  public function can(xfDocumentHit $result, $method, array $args = array())
  {
    return $this->can;
  }

  public function respond(xfDocumentHit $result, $method, array $args = array())
  {
    return $this->response;
  }
}
