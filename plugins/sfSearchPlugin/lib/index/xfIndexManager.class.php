<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * The index manager stores all the indices and provides a singleton access point.
 *
 * Note: You do not have to use the index manager.  The index manager provides a
 * way of maintaining a singleton of the indices.
 *
 * @todo Perhaps find a better way to do this.  The problem is in Propel
 * behaviors because each behavior needs an instance and we can't have dozens of
 * of the same index floating around.  Perhaps a goal for symfony 1.2.
 *
 * @package sfSearch
 * @subpackage Index
 * @author Carl Vondrick
 */
final class xfIndexManager
{
  /**
   * The indices registered.
   *
   * @var array
   */
  static private $indices = array();

  /**
   * Gets an index from the singleton
   *
   * @param string $name The index name
   */
  static public function get($name)
  {
    if (!isset(self::$indices[$name]))
    {
      $r = new ReflectionClass($name);
      if (!$r->isSubclassOf(new ReflectionClass('xfIndex')))
      {
        throw new xfException('xfIndexManager can only handle instances of xfIndex');
      }

      self::$indices[$name] = new $name;
    }

    return self::$indices[$name];
  }
}
