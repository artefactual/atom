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
 * Serialized array structure groups slugs by class_name:
 *
 * Array
 * (
 *     [QubitInformationObject] => Array
 *         (
 *             [0] => slug1 (string)
 *             etc...
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
    $items = $this->getAllByClassName();
    // Get slug's class name
    $className = $this->getClassNameFromSlug($slug);

    // Add or remove slug to clipboard items
    if (in_array($slug, $items[$className]))
    {
      $items[$className] = array_merge(array_diff($items[$className], array($slug)));

      $added = false;
    }
    else
    {
      $items[$className][] = $slug;

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
   * Get the number of objects in clipboard by object type
   *
   * @return array of counts by object types in the clipboard
   */
  public function countByType()
  {
    $counts = array();

    foreach ($this->getAllByClassName() as $className => $slugArray)
    {
      $counts[$className] = count($slugArray);
    }

    return $counts;
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
   * @param string $type  Specify what class type to clear only, e.g.: 'QubitInformationObject', 'QubitActor', etc.
   */
  public function clear($type = null)
  {
    if (null !== $type)
    {
      $items = $this->getAllByClassName();
      unset($items[$type]);
    }

    $this->storage->remove(self::CLIPBOARD_NAMESPACE);

    if (null !== $type)
    {
      $this->storage->write(self::CLIPBOARD_NAMESPACE, serialize($items));
    }
  }

  /**
   * Gets an array with all the information object slugs in the clipboard
   *
   * @return array Slugs added to the clipboard
   */
  public function getAll()
  {
    $slugArray = array();
    $items = $this->getAllByClassName();

    // Translate array by type into flat array of slugs and return.
    foreach ($items as $type => $slugs)
    {
      $slugArray = array_merge($slugArray, $slugs);
    }

    return $slugArray;
  }

  /**
   * Gets an array with all the information object slugs sorted by class name in the clipboard
   *
   * @return array Slugs added to the clipboard
   */
  public function getAllByClassName()
  {
    $items = array();
    if (null !== $savedItems = $this->storage->read(self::CLIPBOARD_NAMESPACE))
    {
      $items = unserialize($savedItems);
    }

    return $items;
  }

  /**
   * For a given slug, determine the object type and return it
   *
   * @return object class name; return null if unable to determine class name
   */
   private function getClassNameFromSlug($slug)
   {
     $query = 'SELECT o.class_name FROM slug s JOIN object o ON s.object_id=o.id WHERE s.slug=' . '"' . $slug . '"';
     $statement = QubitPdo::prepareAndExecute($query);
     $result = $statement->fetch(PDO::FETCH_OBJ);

     if ($result)
     {
       return $result->class_name;
     }
   }
}
