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

class QubitSlug extends BaseSlug
{
  public static function random($length = 12)
  {
    $separator = sfConfig::get('app_separator_character', '-');

    // Adapted from http://stackoverflow.com/questions/5615490/random-code-generator/5615957#5615957
    $alphabet = '23456789abcdefghkmnpqrstwxyz';
    $alphabet_size = strlen($alphabet);

    $block_length = 4;
    $num_blocks = $length / $block_length;

    $slug = '';
    for ($i = 0; $i < $num_blocks; $i++) {
      for ($j = 0; $j < $block_length; $j++) {
        $slug .= $alphabet[mt_rand(0, $alphabet_size - 1)];
      }

      if ($i != $num_blocks - 1) {
        $slug .= $separator;
      }
    }

    return $slug;
  }

  public static function slugify($slug)
  {
    // Handle exotic characters gracefully
    $slug = iconv('utf-8', 'ascii//TRANSLIT', $slug);

    $slug = strtolower($slug);

    // Remove apostrophes
    $slug = preg_replace('/\'/', '', $slug);

    // Allow only digits, letters, and dashes.  Replace sequences of other
    // characters with dash
    $slug = preg_replace('/[^0-9a-z]+/', '-', $slug);

    // Drop (English) articles
    $slug = "-$slug-";
    $slug = str_replace('-a-', '-', $slug);
    $slug = str_replace('-an-', '-', $slug);
    $slug = str_replace('-the-', '-', $slug);

    $slug = trim($slug, '-');

    return $slug;
  }

  public static function getUnique($connection = null)
  {
    if (!isset($connection))
    {
      $connection = QubitTransactionFilter::getConnection(QubitObject::DATABASE_NAME);
    }

    // Try a max of 10 times before giving up (avoid infinite loops when
    // possible slugs exhausted)
    for ($i = 0; $i < 10; $i++)
    {
      $slug = self::random();

      $statement = $connection->prepare('
        SELECT COUNT(*)
        FROM '.QubitSlug::TABLE_NAME.'
        WHERE '.QubitSlug::SLUG.' = ?;');
      $statement->execute(array($slug));

      if (0 == $statement->fetchColumn(0))
      {
        return $slug;
      }
    }
  }

  public static function getByObjectId($id, array $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitSlug::OBJECT_ID, $id);

    if (1 == count($query = self::get($criteria, $options)))
    {
      return $query[0];
    }
  }
}
