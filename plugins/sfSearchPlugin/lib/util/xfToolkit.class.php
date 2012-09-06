<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A toolkit of common algorithms.
 *
 * Some of this code is duplicated from symfony's sfInflector.  This duplication
 * is neccessary because sfSearch needs to be able to stand on its own.
 *
 * @package sfSearch
 * @subpackage Util
 * @author Carl Vondrick
 */
final class xfToolkit
{
  /**
   * Creates an underscored version of the camel input
   *
   * @param string $camel
   * @returns string
   */
  static public function underscore($camel)
  {
    $camel = preg_replace('/([A-Z]+)([A-Z][a-z])/', '$1_$2', $camel);
    $camel = preg_replace('/([a-z\d])([A-Z])/', '$1_$2', $camel);
    
    return strtolower($camel);
  }

  /**
   * Creates a camelized version of underscore input
   *
   * @param string $underscore
   * @returns string
   */
  static public function camelize($underscore)
  {
    return preg_replace('/(^|_)(.)/e', "strtoupper('$2')", $underscore);
  }
}
