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
    $markdownMap = array(
      'bolditalic' => '___',
      'italic' => '_',
      'bold' => '__',
    );

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
