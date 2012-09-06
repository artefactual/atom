<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require 'service/xfIdentifier.interface.php';

/**
 * A mock identifier.
 *
 * @package xfSearch
 * @subpackage Mock
 */
class xfMockIdentifier implements xfIdentifier
{
  public $name = 'foobar', $match = xfIdentifier::MATCH_YES, $objects = array('foo', 'bar', 'baz');

  public function getName()
  {
    return $this->name;
  }

  public function getGuid($input)
  {
    return md5($input);
  }

  public function match($input)
  {
    return $this->match;
  }

  public function discover($count)
  {
    if ($count > 0)
    {
      return array();
    }

    return $this->objects;
  }
}
