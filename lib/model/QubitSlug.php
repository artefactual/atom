<?php

/*
 * This file is part of the AccesstoMemory (AtoM) software.
 *
 * AccesstoMemory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AccesstoMemory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AccesstoMemory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitSlug extends BaseSlug
{
  public static function random()
  {
    $slug = null;

    $alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';

    // Probability of generating a collision can be found using this formula
    // http://en.wikipedia.org/wiki/Birthday_paradox#Cast_as_a_collision_problem
    //
    // Force max random value of 2^31-1 (32-bit signed integer max).
    //
    // With 2^31 possible values, the probability of collision is >50% when we
    // reach approx. 50,000 records
    $rand = mt_rand(0, pow(2, 31)-1);

    // Convert $rand to base36 hash
    while (36 < $rand)
    {
      $slug .= $alphabet[$rand % 36];
      $rand /= 36;
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
}
