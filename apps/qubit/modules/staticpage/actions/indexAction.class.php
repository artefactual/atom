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

    if (sfConfig::get('app_enable_institutional_scoping') && $this->resource->slug == 'home')
    {
      // Remove the search-realm attribute
      $this->context->user->removeAttribute('search-realm');
    }
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
    $content = QubitHtmlPurifier::getInstance()->purify($content);

    $cache->set($cacheKey, $content);

    return $content;
  }
}
