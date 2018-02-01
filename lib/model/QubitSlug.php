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
    SLUG_BASIS_IDENTIFIER = 3,

    SLUG_RESTRICTIVE = 0,
    SLUG_PERMISSIVE = 1;

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
  public static function slugify($slug, $creationType = null)
  {
    // 0, 1, or null
    $slugCreation = (null === $creationType) ? sfConfig::get('app_permissive_slug_creation', QubitSlug::SLUG_RESTRICTIVE) : $creationType;

    switch ($slugCreation)
    {
      case QubitSlug::SLUG_PERMISSIVE:
        // Remove apostrophes
        $slug = preg_replace('/\'/', '', $slug);
        // Whitelist - ASCII A-Za-z0-9, unicode letters, - _ ~ : ; , = * @
        $asciiSet = 'a-zA-Z0-9';
        $extraSet = '\-_~\:;,=\*@';
        $slug = preg_replace('/[^' . $asciiSet . self::getRfc3987Set() . $extraSet . ']+/u', '-', $slug);
        // Remove repeating dashes - replace with single dash.
        $slug = preg_replace('/-+/u', '-', $slug);

        break;

      case QubitSlug::SLUG_RESTRICTIVE:
      default:
        // Handle exotic characters gracefully.
        // TRANSLIT doesn't work in musl's iconv, see #9855.
        if ((false !== $result = iconv('utf-8', 'ascii//TRANSLIT', $slug)) || (false !== $result = iconv('utf-8', 'ascii', $slug)))
        {
          $slug = $result;
        }

        // Remove apostrophes
        $slug = preg_replace('/\'/', '', $slug);
        $slug = strtolower($slug);
        // Allow only digits, letters, and dashes.  Replace sequences of other
        // characters with dash
        $slug = preg_replace('/[^0-9a-z]+/', '-', $slug);
    }

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

  public static function getRfc3987Set()
  {
    // From RFC 3987 IRI allowed chars. Not guaranteed to match \p{L}\p{Nd}.
    return ('\x{00A0}-\x{D7FF}'.'\x{F900}-\x{FDCF}'.'\x{FDF0}-\x{FFEF}'.
            '\x{10000}-\x{1FFFD}'.'\x{20000}-\x{2FFFD}'.'\x{30000}-\x{3FFFD}'.
            '\x{40000}-\x{4FFFD}'.'\x{50000}-\x{5FFFD}'.'\x{60000}-\x{6FFFD}'.
            '\x{70000}-\x{7FFFD}'.'\x{80000}-\x{8FFFD}'.'\x{90000}-\x{9FFFD}'.
            '\x{A0000}-\x{AFFFD}'.'\x{B0000}-\x{BFFFD}'.'\x{C0000}-\x{CFFFD}'.
            '\x{D0000}-\x{DFFFD}'.'\x{E0000}-\x{EFFFD}');
  }
}
