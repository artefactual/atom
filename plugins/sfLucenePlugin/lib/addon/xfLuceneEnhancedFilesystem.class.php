<?php
/**
 * This file is part of the sfLucene package.
 * (c) Carl Vondrick <carl.vondrick@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * An enhanced Filesystem object to chmod correctly.
 *
 * @package sfLucene
 * @subpackage Addon
 * @author Carl Vondrick
 */
final class xfLuceneEnhancedFilesystem extends Zend_Search_Lucene_Storage_Directory_Filesystem 
{
  public function createFile($filename)
  {
    try
    {
      parent::createFile($filename);
    }
    catch (Zend_Search_Lucene_Exception $e)
    {
      if (false === strpos($e->getMessage(), 'chmod'))
      {
        throw $e;
      }
    }

    return $this->_fileHandlers[$filename];
  }
}

