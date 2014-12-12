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

class StaticPageIndexAction extends sfAction
{
  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    if (1 > strlen($title = $this->resource->__toString()))
    {
      $title = $this->context->i18n->__('Untitled');
    }

    $this->response->setTitle("$title - {$this->response->getTitle()}");

    $this->content = $this->getPurifiedStaticPageContent();
  }

  protected function getPurifiedStaticPageContent()
  {
    $culture = sfContext::getInstance()->getUser()->getCulture();
    $cacheKey = 'staticpage:'.$this->resource->id.':'.$culture;
    $cache = QubitCache::getInstance();

    if (null === $cache)
    {
      return;
    }

    if ($cache->has($cacheKey))
    {
      return $cache->get($cacheKey);
    }

    $content = $this->resource->getContent(array('cultureFallback' => true));

    // Prepare htmlpurifier cache directory
    $purifierCacheDirectory = sfConfig::get('sf_cache_dir').DIRECTORY_SEPARATOR.'htmlpurifier';
    if (!file_exists($purifierCacheDirectory))
    {
      mkdir($purifierCacheDirectory, 0770, true);
    }

    // Load htmlpurifier
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
    $purifier = new HTMLPurifier($config);

    $content = $purifier->purify($content);

    $cache->set($cacheKey, $content);

    return $content;
  }
}
