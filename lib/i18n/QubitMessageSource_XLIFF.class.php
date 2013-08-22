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
 * Changes the way that XLIFFs documents are generated for a better integration
 * with Transifex. This class is intended to be used by i18nConsolidateTask.
 *
 * @see sfMessageSource_XLIFF
 */
class QubitMessageSource_XLIFF extends sfMessageSource_XLIFF
{
  /**
   * Override its parent method in order to: (1) avoid appending units if the
   * target document exists, (2) guarantee the singularity of the id property
   * in the trans-unit elements based in the checksum of its source element.
   * This is needed so Transifex won't overwrite existing source strings when
   * using autonumeric ids and they clash.
   *
   * @see sfMessageSource_XLIFF
   */
  public function save($catalogue = 'messages')
  {
    $messages = $this->untranslated;
    if (count($messages) <= 0)
    {
      return false;
    }

    $variants = $this->getVariants($catalogue);
    if ($variants)
    {
      list($variant, $filename) = $variants;
    }
    else
    {
      list($variant, $filename) = $this->createMessageTemplate($catalogue);
    }

    if (is_writable($filename) == false)
    {
      throw new sfException(sprintf("Unable to save to file %s, file must be writable.", $filename));
    }

    // Create new DOM
    $dom = $this->createDOMDocument();
    $this->createMessageTemplate($catalogue);
    $dom->load($filename);

    // Locate body tag
    $xpath = new DomXPath($dom);
    $body = $xpath->query('//body')->item(0);

    // For each message add it to the XML file using DOM
    foreach ($messages as $message)
    {
      $unit = $dom->createElement('trans-unit');

      // Set the ID using the SHA1 checksum of its source
      $unit->setAttribute('id', sha1($message));

      // Set source
      $source = $dom->createElement('source');
      $source->appendChild($dom->createTextNode($message));
      $unit->appendChild($source);

      // Set target
      $target = $dom->createElement('target');
      $target->appendChild($dom->createTextNode(''));
      $unit->appendChild($target);

      // Append to <body/>
      $body->appendChild($unit);
    }

    // Update date
    $fileNode = $xpath->query('//file')->item(0);
    $fileNode->setAttribute('date', @date('Y-m-d\TH:i:s\Z'));

    $dom = $this->createDOMDocument($dom->saveXML());

    // save it and clear the cache for this variant
    $dom->save($filename);
    if ($this->cache)
    {
      $this->cache->remove($variant.':'.$this->culture);
    }

    return true;
  }
}
