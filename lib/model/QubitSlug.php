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
  const
    SLUG_BASIS_TITLE = 0,
    SLUG_BASIS_REFERENCE_CODE = 1,
    SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO = 2,
    SLUG_BASIS_IDENTIFIER = 3;

  public static function random($length = 12)
  {
    $separator = '-';

    // Adapted from http://stackoverflow.com/questions/5615490/random-code-generator/5615957#5615957
    $alphabet = '23456789abcdefghkmnpqrstwxyz';
    $alphabetSize = strlen($alphabet);

    $blockLength = 4;
    $numBlocks = ceil($length / $blockLength);

    $slug = '';
    for ($i = 0; $i < $numBlocks; $i++)
    {
      for ($j = 0; $j < $blockLength; $j++)
      {
        $slug .= $alphabet[mt_rand(0, $alphabetSize - 1)];
      }

      if ($i != $numBlocks - 1)
      {
        $slug .= $separator;
      }
    }

    return $slug;
  }

  /**
   * Slugify a specified string
   *
   * @param string $slug  The string we want to slugify
   *
   * @param bool $dropArticles  Whether or not to drop English articles from the slug.
   *                            We can disable this when we generate slugs by identifier.
   */
  public static function slugify($slug)
  {
    // Handle exotic characters gracefully.
    // TRANSLIT doesn't work in musl's iconv, see #9855.
    if ((false !== $result = iconv('utf-8', 'ascii//TRANSLIT', $slug)) || (false !== $result = iconv('utf-8', 'ascii', $slug)))
    {
      $slug = $result;
    }

    $slug = strtolower($slug);

    // Remove apostrophes
    $slug = preg_replace('/\'/', '', $slug);

    // Allow only digits, letters, and dashes.  Replace sequences of other
    // characters with dash
    $slug = preg_replace('/[^0-9a-z]+/', '-', $slug);

    $slug = "-$slug-";

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
