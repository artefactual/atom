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
 * Import an XML document into Qubit.
 *
 * @package    AccesstoMemory
 * @subpackage library
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class QubitMarkdown
{
  protected static
    $instance = null,
    $markdownMap = array(
      'bolditalic' => '___',
      'italic' => '_',
      'bold' => '__',
    );

  private
    $enabled;

  protected function __construct()
  {
    $this->enabled = sfConfig::get('app_markdown_enabled', false);

    if (!$this->enabled)
    {
      return;
    }

    $this->parsedown = new ParsedownExtra();
  }

  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new QubitMarkdown;
    }

    return self::$instance;
  }

  public function parse($content, $options = array())
  {
    if (!$this->enabled)
    {
      return $content;
    }

    // Use safe mode by default
    $safeMode = true;
    if (isset($options['safeMode']))
    {
      $safeMode = $options['safeMode'];
    }

    // Use text method by default,
    // which adds paragraph elements.
    $method = 'text';
    if (isset($options['inline']) && $options['inline'])
    {
      $method = 'line';
    }

    $this->parsedown->setSafeMode($safeMode);
    $content = $this->parsedown->$method($content);

    return $content;
  }

  public function strip($content)
  {
    if (!$this->enabled)
    {
      return $content;
    }

    // TODO: Parsedown has been recently updated to 1.8.0-beta-1,
    // but ParsedownExtra still have some issues with that version,
    // so we're still using the 1.7.1 version. Once both libraries
    // are in a more stable version, we should extend them in a new
    // QubitParsedownExtra class, where we should create an strip
    // method that extends both text methods from the libraries.
    // This method should use the new `$Elements` variable, added in
    // 1.8.x in the Parsedown text method, to get the text value from
    // the elements before they are converted to markup.

    // Convert Markdown to HTML
    $this->parsedown->setSafeMode(true);
    $content = $this->parsedown->text($content);

    // Remove all tags
    $content = strip_tags($content);

    return $content;
  }

  /**
   * Convert an EAD markup tag to it's corresponding markdown symbols.
   *
   * @return ead string with markdown replacement
   */
  public static function eadTagToMarkdown($eadTag, $node)
  {
    switch ($eadTag)
    {
      // EAD tags that we want to convert to markdown.
      case 'emph':
        // Set default to italic when no render attribute is present.
        $markdownSymbol = (!empty($node->getAttribute('render')))
          ? QubitMarkdown::$markdownMap[$node->getAttribute('render')]
          : QubitMarkdown::$markdownMap['italic'];
        return $markdownSymbol . $node->nodeValue . $markdownSymbol;
    }
  }
}
