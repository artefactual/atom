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
 * Representation of the user clipboard.
 * This class does not lock the session storage to prevent concurrent writes.
 *
 * @package    AccesstoMemory
 * @subpackage libraries
 */
class QubitClipboard
{
  const CLIPBOARD_NAMESPACE = 'symfony/user/sfUser/clipboard';

  private $storage;

  public function __construct(sfStorage $storage)
  {
    $this->storage = $storage;
  }

  /**
   * Adds or removes slug to clipboard items
   *
   * @param  string $slug Information object slug
   *
   * @return boolean True if added, false if removed
   */
  public function toggle($slug)
  { 
    // Get actual items
    $items = $this->getAll();

    // Add or remove slug to clipboard items
    if (in_array($slug, $items))
    {
      $items = array_merge(array_diff($items, array($slug)));

      $added = false;
    }
    else
    {
      $items[] = $slug;

      $added = true;
    }

    // Save clipboard items in storage
    $this->storage->write(self::CLIPBOARD_NAMESPACE, serialize($items));

    return $added;
  }

  /**
   * Gets the amount of information objects added to the clipboard
   *
   * @return int Count of information objects in the clipboard
   */
  public function count()
  { 
    return count($this->getAll());
  }

  /**
   * Checks if a slug is added to the clipboard
   *
   * @param  string $slug Information object slug
   *
   * @return boolean True if added, false if not added
   */
  public function has($slug)
  {
    return in_array($slug, $this->getAll());
  }

  /**
   * Removes clipboard namespace from storage
   */
  public function clear()
  {
    $this->storage->remove(self::CLIPBOARD_NAMESPACE);
  }

  /**
   * Gets an array with all the information object slugs in the clipboard
   *
   * @return array Slugs added to the clipboard
   */
  public function getAll()
  {
    $items = array();
    if (null !== $savedItems = $this->storage->read(self::CLIPBOARD_NAMESPACE))
    {
      $items = unserialize($savedItems);
    }

    return $items;
  }
}
