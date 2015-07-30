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
 * SitemapWriterSection
 */
class SitemapWriterSection
{
  /**
   * Maximum number of items permitted in a Sitemap XML document.
   */
  const MAX_ITEMS_SITEMAP = 50000;

  /**
   * Maximum size of a Sitemap XML document in bytes.
   * We are going to ignore this for now, hard to reach 50 MiB
   * with just 50K records, and gzipped.
   */
  const MAX_SIZE_SITEMAP = 52428800;

  /**
   * The size of the batches that we command XmlWriter to write
   */
  const BUFFER_LIMIT = 500;

  /**
   * Instance of XMLWriter
   */
  private $writer;

  /**
   * Instance of XMLWriter using memory
   */
  private $buffer;

  /**
   * Total size
   */
  private $size = 0;

  /**
   * Size of the buffer
   */
  private $bufferSize = 0;

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
   * Is the document ended?
   */
  private $ended = false;

  /**
   * Constructor
   */
  public function __construct($file, $index, $baseUrl, $indent, $compress)
  {
    $this->indent = $indent;
    $this->baseUrl = $baseUrl;
    $this->compress = $compress;

    $parts = pathinfo($file);
    $this->file = $parts['dirname'].DIRECTORY_SEPARATOR.$parts['filename'].'.'.$index.'.'.$parts['extension'];

    // __destructor() doesn't get called on fatal errors
    register_shutdown_function(array($this, 'end'));

    $this->writer = new XMLWriter;
    $this->writer->openUri($this->file);
    $this->writer->setIndent($this->indent);
    $this->writer->startDocument('1.0', 'UTF-8');
    $this->writer->startElement('urlset');
    $this->writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $this->writer->writeAttribute('xsi:schemaLocation', 'http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
    $this->writer->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

    // Create in-memory intermediate XML writer to avoid continuous disk access
    $this->buffer = new XMLWriter;
    $this->buffer->openMemory();
    $this->buffer->setIndent($this->indent);
  }

  public function add(AbstractSitemapUrl $item)
  {
    if (self::BUFFER_LIMIT > 0 && $this->bufferSize === self::BUFFER_LIMIT)
    {
      $this->flush();
    }

    $this->buffer->writeRaw($item->getUrl($this->baseUrl, $this->indent));
    $this->bufferSize++;
    $this->size++;
  }

  public function isFull()
  {
    return $this->size === self::MAX_ITEMS_SITEMAP;
  }

  public function getLocation()
  {
    $ext = $this->compress ? '.gz' : '';

    return $this->baseUrl.'/'.basename($this->file).$ext;
  }

  private function flush()
  {
    if ($this->bufferSize === 0)
    {
      return;
    }

    $batch = $this->buffer->outputMemory();
    $this->writer->writeRaw($batch);

    $this->bufferSize = 0;
  }

  public function end()
  {
    if ($this->ended)
    {
      return;
    }

    $this->flush();

    $this->writer->endElement(); // </sitemap>
    $this->writer->endDocument();

    if ($this->compress && Qubit::gzipFile($this->file))
    {
      unlink($this->file);
    }

    $this->ended = true;
  }
}
