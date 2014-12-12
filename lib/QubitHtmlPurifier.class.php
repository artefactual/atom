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

class QubitHtmlPurifier
{
  static protected
    $instance = null;

  private
    $enabled;

  protected function __construct()
  {
    $this->enabled = sfConfig::get('app_htmlpurifier_enabled', true);

    if (!$this->enabled)
    {
      return;
    }

    // Prepare htmlpurifier cache directory
    $purifierCacheDirectory = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'htmlpurifier';
    if (!file_exists($purifierCacheDirectory))
    {
      mkdir($purifierCacheDirectory, 0770, true);
    }

    // Load htmlpurifier library
    require sfConfig::get('sf_root_dir').'/vendor/htmlpurifier/library/HTMLPurifier.includes.php';

    $config = HTMLPurifier_Config::createDefault();
    $config->set('Core', 'Encoding', sfConfig::get('sf_charset', 'UTF-8'));
    $config->set('Cache.SerializerPath', $purifierCacheDirectory);
    $config->set('AutoFormat.AutoParagraph', true);
    $config->set('HTML', 'Doctype', 'XHTML 1.1');
    $config->set('HTML.Allowed', implode(',', array(
      'div', 'span', 'p',
      'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
      'strong', 'em',
      'abbr[title]', 'acronym', 'address',
      'blockquote', 'cite', 'code',
      'pre', 'br',
      'a[href]', 'img[src]',
      'ul', 'ol', 'li',
      'dl', 'dt', 'dd',
      'table', 'tr', 'td', 'th',
      'tbody', 'thead', 'tfoot',
      'col', 'colgroup', 'caption',
      'b', 'i', 'tt',
      'sub', 'sup', 'big', 'small', 'hr')));
    $config->set('HTML.AllowedAttributes', implode(',', array(
      'class', 'title', 'src', 'href')));

    $this->purifier = new HTMLPurifier($config);
  }

  static public function getInstance()
  {
    if (!isset(self::$instance))
    {
      self::$instance = new QubitHtmlPurifier;
    }

    return self::$instance;
  }

  public function purify($content)
  {
    if (!$this->enabled)
    {
      return $content;
    }

    return $this->purifier->purify($content);
  }

  // __call ...
  // return call_user_func_array(array($this->purifier, 'purify'), $args);
}
