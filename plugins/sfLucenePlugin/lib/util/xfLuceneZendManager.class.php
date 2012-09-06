<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Manages the loading of Zend_Search_Lucene
 *
 * @package sfLucene
 * @subpackage Utilities
 * @author Carl Vondrick
 */
final class xfLuceneZendManager
{
  /**
   * Flag to indicate if Zend_Search_Lucene is loaded
   *
   * @var bool
   */
  static private $loaded = false;

  /**
   * Configures and loads Zend_Search_Lucene
   */
  static public function load()
  {
    if (!self::$loaded)
    {
      set_include_path(get_include_path() . PATH_SEPARATOR . realpath(dirname(__FILE__) . '/../vendor/'));

      // this is an ugly hack, but there's a bug somewhere with sfSimpleAutoload,
      // Zend_Search_Lucene, and PHP.
      require_once 'Zend/Search/Lucene.php';

      self::$loaded = true;
    }
  }
}
