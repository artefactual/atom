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
 * Instantiable class for Markdown operations.
 *
 * @author     Steve Breker <sbreker@artefactual.com>
 */
class QubitMarkdown
{
    protected static $instance = null;
    protected static $markdownMap = [
        'bolditalic' => '___',
        'italic' => '_',
        'bold' => '__',
    ];

    private $enabled;

    protected function __construct()
    {
        $this->enabled = sfConfig::get('app_markdown_enabled', true);

        if (!$this->enabled) {
            return;
        }

        $this->parsedown = new ParsedownExtra();
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new QubitMarkdown();
        }

        return self::$instance;
    }

    /**
     * Based on the app_markdown_enabled setting, parses the string value
     * of the sent content to convert Markdown syntax to HTML. Returns the
     * value without parsing when the setting is not enabled and parses the
     * content in safe mode by default when it's enabled. Safe mode can be
     * disabled with an option to parse existing HTML content, like in the
     * static pages. It uses Parsedown `text` method by default, which adds
     * paragraphs, but this can be changed with the `inline` option, to use
     * Parsedown `line` method.
     *
     * @param mixed $content object/String to parse
     * @param array $options set of options (safe mode and inline)
     *
     * @return string parsed content
     */
    public function parse($content, $options = [])
    {
        $content = $this->getUnescapedString($content);

        if (!$this->enabled || 0 == strlen($content)) {
            return $content;
        }

        // Use safe mode by default
        $safeMode = true;
        if (isset($options['safeMode'])) {
            $safeMode = $options['safeMode'];
        }

        // Use text method by default,
        // which adds paragraph elements.
        $method = 'text';
        if (isset($options['inline']) && $options['inline']) {
            $method = 'line';
        }

        $this->parsedown->setSafeMode($safeMode);

        return $this->parsedown->{$method}($content);
    }

    /**
     * Based on the app_markdown_enabled setting, removes the Markdown and
     * HTML tags of the sent content. Returns the string value of the content
     * like it is when the setting is not enabled, otherwise it parses it to
     * HTML using Parsedown `text` method and then removes all tags with PHP
     * `strip_tags` function.
     *
     * @param mixed $content object/String to parse
     *
     * @return string stripped content
     */
    public function strip($content)
    {
        $content = $this->getUnescapedString($content);

        if (!$this->enabled || 0 == strlen($content)) {
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
        return strip_tags($content);
    }

    /**
     * Convert an EAD markup tag to it's corresponding markdown symbols.
     *
     * @param mixed $eadTag
     * @param mixed $node
     *
     * @return ead string with markdown replacement
     */
    public static function eadTagToMarkdown($eadTag, $node)
    {
        // Don't convert if Markdown is not enabled
        if (!sfConfig::get('app_markdown_enabled', true)) {
            return $node->nodeValue;
        }

        switch ($eadTag) {
            // EAD tags that we want to convert to markdown.
            case 'emph':
                // Set default to italic when no render attribute is present.
                $markdownSymbol = (!empty($node->getAttribute('render')))
                    ? QubitMarkdown::$markdownMap[$node->getAttribute('render')]
                    : QubitMarkdown::$markdownMap['italic'];

                return $markdownSymbol.$node->nodeValue.$markdownSymbol;
        }
    }

    /**
     * Returns the string value of the content. If content is an object
     * it calls `__toString` only if that method exists, otherwise it
     * returns an empty string, like it does with everything else that
     * is not an object or a string.
     *
     * We normally reach this function from the templates and, with the
     * escaping strategy enabled by default, the content will be escaped.
     *
     * @param mixed $content object/String to normalize
     *
     * @return string string value of content or empty string
     */
    protected function getUnescapedString($content)
    {
        $content = sfOutputEscaper::unescape($content);

        if (is_string($content)) {
            return $content;
        }

        if (is_object($content) && method_exists($content, '__toString')) {
            return $content->__toString();
        }

        return '';
    }
}
