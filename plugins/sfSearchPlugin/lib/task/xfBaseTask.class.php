<?php
/**
 * This file is part of the sfSearch package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * A base task for xfSearch tasks.
 *
 * @package sfSearch
 * @subpackage Task
 * @author Carl Vondrick
 */
abstract class xfBaseTask extends sfBaseTask
{
  /**
   * Checks to see if index exists.
   *
   * @param string $index The index name
   * @throws sfException if index does not exist
   */
  protected function checkIndexExists($index)
  {
    if (!class_exists($index, true))
    {
      throw new sfException('Index "' . $index . '" does not exist');
    }

    $ref = new ReflectionClass($index);
    if (!$ref->implementsInterface('xfIndex'))
    {
      throw new sfException('Class "' . $index . '" does not implement xfIndex interface.');
    }
  }
}
