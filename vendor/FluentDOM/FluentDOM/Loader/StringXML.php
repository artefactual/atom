<?php
/**
* Load FluentDOM from XML string
*
* @version $Id: StringXML.php 431 2010-03-29 20:42:04Z subjective $
* @license http://www.opensource.org/licenses/mit-license.php The MIT License
* @copyright Copyright (c) 2009 Bastian Feder, Thomas Weinert
*
* @package FluentDOM
* @subpackage Loaders
*/

/**
* include interface
*/
require_once(dirname(__FILE__).'/../Loader.php');

/**
* Load FluentDOM from XML string
*
* @package FluentDOM
* @subpackage Loaders
*/
class FluentDOMLoaderStringXML implements FluentDOMLoader {

  /**
  * Load DOMDocument from xml string
  *
  * @param string $source xml string
  * @param string $contentType
  * @return DOMDocument|FALSE
  */
  public function load($source, $contentType) {
    if (is_string($source) &&
        FALSE !== strpos($source, '<') &&
        $contentType == 'text/xml') {
      $dom = new DOMDocument();
      $dom->loadXML($source);
      return $dom;
    }
    return FALSE;
  }
}

?>