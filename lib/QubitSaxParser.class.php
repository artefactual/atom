<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Simplified SAX parser base class
 * --------------------------------
 *
 * This class can be used as a parent class for classes that need to access
 * XML data.
 *
 * The basic idea is the child class defines a method for each XML tag that
 * needs to be accessed.
 *
 * The parser calls class methods named after tags once the tag's descendents
 * are parsed.
 *
 * For example: </chapter> will trigger execution of $this->chapterTag()
 *
 * If something needs to be done before the tag's descendents are processed
 * (like creating a file or object in which data will be placed in) a method
 * can be named with the suffix "TagInit".
 *
 * For example: <chapter> will trigger execution of $this->chapterTagInit()
 *
 * Current tag data can be accessed from these methods using these methods:
 *
 *   tag()                  Returns current tag's name
 *   data()                 Returns current tag's cdata
 *   attr(<attribute name>) Returns a current tag's attribute value
 *
 * If a tag is used in more than one context, methods can be used to check
 * the path, within the XML hierarchy, to the element:
 *
 *   path()                        Returns path to current element
 *   pathIncludes(<path fragment>) Check if current path contains subpath
 *
 * @package AccesstoMemory
 * @subpackage model
 * @author Mike Cantelon <mike@artefactual.com>
 */
class QubitSaxParser
{
  protected $sax = null;
  protected $error = null;

  // Ancestor tag data stack
  protected $ancestors = array();

  // Nesting level bookkeeping
  protected $level             = 0;
  protected $lastTagStartLevel = 1;
  protected $lastTagEndLevel   = 1;

  // Get set to properties of current tag being processed
  protected $tag;
  protected $data;
  protected $attrStack = array();

  // Keep note of which handlers have been triggered
  protected $triggeredHandlers = array();

  public function __construct($encoding = 'UTF-8')
  {
    $this->sax = xml_parser_create($encoding);

    // No auto case conversion and don't skip whitespace
    xml_parser_set_option($this->sax, XML_OPTION_CASE_FOLDING, false);
    xml_parser_set_option($this->sax, XML_OPTION_SKIP_WHITE, false);

    // Set up SAX handlers
    xml_set_object($this->sax, $this);
    xml_set_element_handler($this->sax, 'startTagInternalHandler', 'endTagInternalHandler');
    xml_set_character_data_handler($this->sax, 'tagDataInternalHandler');
  }

  /**
   * Handle start tags
   *
   * The start tag handler:
   *
   * 1) Stores ancestor data if descending in tag hierarchy
   * 2) Stores current tag data
   * 3) Calls a method with suffix "TagInit" if it exists...
   *    otherwise, it calls a method called "startTagHandler"
   *
   * @param object $sax  SAX parser
   * @param string $tag  tag name of element currently being processed
   * @param array $array  array of attribute data
   *
   * @return void
   */
  protected function startTagInternalHandler($sax, $tag, $attr)
  {
    $this->level++;
    $this->storeAncestorIfNesting();
    $this->lastTagStartLevel = $this->level;

    // Store current tag data
    $this->tag = $tag;
    unset($this->data);

    array_push($this->attrStack, $attr);

    // Methods that end with "StartTag" are handlers for specific tags...
    // Call tag handler, if it exists, or generic tag handler
    $this->callOptionalTagHandlers($tag, 'TagInit', 'startTagHandler');
  }

  /**
   * Attempt to call tag handler by suffix, or default, if they exist
   *
   * Take note of which handlers have been called
   *
   * @param string $tag  tag name of element currently being processed
   * @param string $handlerMethodSuffix  handler naming scheme suffix
   * @param string $defaultHandlerMethod  name of generic handler
   *
   * @return void
   */
  protected function callOptionalTagHandlers($tag, $handlerMethodSuffix, $defaultHandlerMethod)
  {
    $executeMethod = null;

    // Determine if a method should be called
    $tagMethod = $tag . $handlerMethodSuffix;

    if (method_exists($this, $tagMethod))
    {
      $executeMethod = $tagMethod;
    }
    else if (method_exists($this, $defaultHandlerMethod))
    {
      $executeMethod = $tag . $handlerMethodSuffix;
    }

    // call method and log that is has been called
    if (isset($executeMethod))
    {
      $this->logTriggeredHandler($executeMethod);
      call_user_func(array($this, $executeMethod));
    }
  }

  /**
   * Note that a handler method has been triggered
   *
   * @param string $method  name of handler method
   *
   * @return void
   */
  protected function logTriggeredHandler($method)
  {
    if (!in_array($method, $this->triggeredHandlers))
    {
      $this->triggeredHandlers[] = $method;
    }
  }

  /**
   * If descending the hierarchy, store ancestor data
   *
   * @return void
   */
  protected function storeAncestorIfNesting()
  {
    // If we're one level deeper than last start tag, store ancestor
    if ($this->level > $this->lastTagStartLevel)
    {
      $ancestorData = array(
        'tag'  => $this->tag,
        'attr' => $this->currentAttr(),
        'data' => $this->data
      );

      array_push($this->ancestors, $ancestorData);
    }
  }

  /**
   * Handle tag CDATA
   *
   * Note that this gets executed even for whitespace between tags.
   *
   * @param object $sax  SAX parser
   * @param string $data  CDATA text
   *
   * @return void
   */
  protected function tagDataInternalHandler($sax, $data)
  {
    if (isset($this->data))
    {
      $this->data .= $data;
    }
    else {
      $this->data = $data;
    }
  }

  /**
   * Handle end tags
   *
   * The end tag handler:
   *
   * 1) Discards ancestor data if ascending in tag hierarchy
   * 2) Calls a method with suffix "Tag" if it exists...
   *    otherwise, it calls a method called "endTagHandler"
   * 3) Discards current tag data
   *
   * @param object $sax  SAX parser
   * @param string $tag  tag name of element currently being processed
   *
   * @return void
   */
  protected function endTagInternalHandler($sax, $tag)
  {
    $this->level--;

    // If we're one level less deep than last end tag, dump ancestor
    if ($this->level < $this->lastTagEndLevel)
    {
      array_pop($this->ancestors);
    }
    $this->lastTagEndLevel = $this->level;

    // Methods that end with "EndTag" are handlers for specific tags...
    // Call tag handler, if it exists, or generic tag handler
    $this->callOptionalTagHandlers($tag, 'Tag', 'endTagHandler');

    // Discard this tag's attribute data
    array_pop($this->attrStack);
  }

  /**
   * Parse contents of file
   *
   * The parser can optionally not be freed after a parse, if one wants to
   * inspect error state externally.
   *
   * @param string $file  file path of XML to parse
   * @param boolean $freeAfterParse  whether to free parser after parse
   *
   * @return void
   */
  public function parse($file, $freeAfterParse = true)
  {
    $fp = fopen($file, 'r');

    if (!$fp)
    {
      $this->error = array(
        'string' => 'Unable to open file'
      );

      return false;
    }

    $success = true;

    // Parse in chunks to preserve memory
    while ($data = fread($fp, 4096))
    {
      if (!xml_parse($this->sax, $data, feof($fp)))
      {
        $success = false;
        break;
      }
    }
    fclose($fp);

    if (!$success)
    {
      $errorCode = xml_get_error_code($this->sax);

      $this->error = array(
        'code'   => $errorCode,
        'string' => xml_error_string($errorCode),
        'line'   => xml_get_current_line_number($this->sax),
        'column' => xml_get_current_column_number($this->sax),
        'byte'   => xml_get_current_byte_index($this->sax)
      );

      $this->error['summary'] = sprintf(
        'Parsing error %d: "%s" at line %d, column %d (byte %d)',
        $this->error['code'],
        $this->error['string'],
        $this->error['line'],
        $this->error['column'],
        $this->error['byte']
      );
    }

    if ($freeAfterParse)
    {
      xml_parser_free($this->sax);
    }

    return $success;
  }

  /**
   * Get parser
   *
   * Can be used to inspect error state externally
   *
   * @return resource  SAX parser
   */
  public function getParser()
  {
    return $this->sax;
  }

  /**
   * Get parsing error description
   *
   * @return string  Description of parsing error
   */
  public function getErrorData()
  {
    return $this->error;
  }


  /*
   *
   *  Helper functions for interpreting parsed data
   *  ---------------------------------------------
   */


  /**
   * Get name of current tag being processed
   *
   * @return string  name of current tag being processed
   */
  protected function tag()
  {
    return $this->tag;
  }

  /**
   * Get CDATA of current tag being processed
   *
   * @return string  CDATA content
   */
  protected function data()
  {
    return $this->data;
  }

  /**
   * Get attribute of current tag being processed
   *
   * @param string $attrName  attribute name
   *
   * @return string  attribute value
   */
  protected function attr($attrName)
  {
    $current = end($this->attrStack);
    return $current[$attrName];
  }

  /**
   * Get all attribute data for current tag being processed
   *
   * @return array  array where key is attribute name
   */
  protected function currentAttr()
  {
    return end($this->attrStack);
  }

  /**
   * Return immediate ancestor
   *
   * @return array  parent tag data
   */
  protected function parent()
  {
    return end(array_values($this->ancestors));
  }

  /**
   * Return path in XML hierarchy
   *
   * @return string  string representation of path
   */
  protected function path()
  {
    $path = array();

    foreach ($this->ancestors as $ancestor)
    {
      array_push($path, $ancestor['tag']);
    }

    return implode('/', $path);
  }

  /**
   * Check if the current path in the XML hierarchy contains a subpath
   *
   * @param string $subPath  XML path or path fragment
   *
   * @return boolean  true if subpath exists in current path
   */
  protected function pathIncludes($subPath)
  {
    return strpos($this->path(), $subPath) !== false;
  }


  /*
   *
   *  Diagnostic functions for troubleshooting parsers
   *  ------------------------------------------------
   */


  /**
   * Return array of handlers defined
   *
   * @return array  array of handlers
   */
  protected function handlers()
  {
    $handlers = array();

    foreach (get_class_methods(get_class($this)) as $method)
    {
      if ($this->methodIsHandler($method))
      {
        $handlers[] = $method;
      }
    }

    sort($handlers);

    return $handlers;
  }

  /**
   * Return array of handlers that weren't triggered during parsing
   *
   * This can be used to make sure the documents you're parsing aren't missing
   * any elements.
   *
   * @return array  array of handlers
   */
  protected function untriggeredHandlers()
  {
    return array_diff($this->handlers(), $this->triggeredHandlers);
  }

  /**
   * Return array of methods defined in child class that are not handlers
   *
   * This can be used to make sure you haven't misnamed any handlers (if
   * a handler is returned by this function then it is likely misnamed).
   *
   * @return array  array of handlers
   */
  protected function nonHandlers()
  {
    $allMethods = get_class_methods(get_class($this));
    $parentClassMethods = get_class_methods(get_parent_class($this));

    return array_diff($allMethods, $parentClassMethods, $this->handlers());
  }

  /**
   * Check if a method is a handler
   *
   * @param string $method  name of method
   *
   * @return boolean  true if method is a handler
   */
  protected function methodIsHandler($method)
  {
    if ($method == 'startTagHandler' || $method == 'endTagHandler')
    {
      return true;
    }

    if (substr($method, -strlen('TagInit')) === 'TagInit')
    {
      return true;
    }
    else if (substr($method, -strlen('Tag')) === 'Tag')
    {
      return true;
    }

    return false;
  }
}
