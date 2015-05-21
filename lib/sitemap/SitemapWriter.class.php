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
 * SitemapWriter 
 */
class SitemapWriter
{
  /**
   * Instance of XMLWriter
   */
  private $writer;

  /**
   * Batch size
   */
  private $batchSize = 0;

  /**
   * Current sitemap
   */
  private $sitemap;

  /**
   * Sitemap index
   */
  private $sitemapIndex = 0;

  /**
   * Base URL
   */
  private $baseUrl;

  /**
   * Indent XML output
   */
  private $indent = false;

  /**
   * Compress XML output
   */
  private $compress = false;

  /**
   * Main document location
   */
  private $file;

  /**
   * Is the document ended?
   */
  private $ended = false;

  /**
   * Configuration object
   */
  private $config;

  /**
   * Configuration path
   */
  private static $configPath = 'config/sitemap.yml';

  /**
   * Constructor
   */
  public function __construct($baseDir, $baseUrl, $indent = false, $compress = false)
  {
    $this->file = $baseDir.DIRECTORY_SEPARATOR.'sitemap.xml';
    $this->indent = $indent;
    $this->baseUrl = $baseUrl;
    $this->compress = $compress;

    $this->loadConfiguration();

    // __destructor() doesn't get called on fatal errors
    register_shutdown_function(array($this, 'end'));

    $this->writer = new XMLWriter;
    $this->writer->openUri($this->file);
    $this->writer->setIndent($this->indent);
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->startElement('sitemapindex');
    $this->writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $this->writer->writeAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
  }

  private function loadConfiguration()
  {
    $configPaths = sfContext::getInstance()->getConfiguration()->getConfigPaths(self::$configPath);
    $this->config = SitemapConfigHandler::getConfiguration($configPaths);
  }

  /**
   * Add all the elements of a given set. The set must be
   * an object extending AbstractSitemapObjectSet.
   */
  public function addSet(AbstractSitemapObjectSet $set)
  {
    $set->setConfig($this->config);
    $set->init();

    foreach ($set as $item)
    {
      $this->getSitemap()->add($item);
    }
  }

  /**
   * getSitemap returns the current SitemapWriterSection instance or
   * instantiate a new one while taking care of updating the index.
   */
  private function getSitemap()
  {
    if (is_null($this->sitemap))
    {
      $this->addSitemap();
    }
    else if ($this->sitemap->isFull())
    {
      $this->sitemap->end();
      $this->addSitemap();
    }

    return $this->sitemap;
  }

  private function addSitemap()
  {
    $this->sitemapIndex++;
    $this->sitemap = new SitemapWriterSection($this->file, $this->sitemapIndex, $this->baseUrl, $this->indent, $this->compress);    

    $this->writer->startElement('sitemap');
    $this->writer->writeElement('loc', $this->sitemap->getLocation());
    $this->writer->writeElement('lastmod', date('c'));
    $this->writer->endElement();
  }

  public function end()
  {
    if ($this->ended)
    {
      return;
    }

    if (null !== $this->sitemap)
    {
      $this->sitemap->end();
    }

    $this->writer->endElement(); // </sitemapindex>
    $this->writer->endDocument();

    $this->ended = true;
  }
}
