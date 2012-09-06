<?php
/**
* Interface for FluentDOM loaders
*
* @version $Id: Loader.php 431 2010-03-29 20:42:04Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*
* @package FluentDOM
* @subpackage Loaders
*/

/**
* Interface for FluentDOM loaders
*
* @package FluentDOM
* @subpackage Loaders
*/
interface FluentDOMLoader {

  /**
  * load FluentDOM document data from a source
  *
  * @param mixed $source
  * @param string $contentType
  * @return DOMDocument|array(DOMDocument,DOMNode)|FALSE
  */
  public function load($source, $contentType);
}

?>