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
    public const SLUG_BASIS_TITLE = 0;
    public const SLUG_BASIS_REFERENCE_CODE = 1;
    public const SLUG_BASIS_REFERENCE_CODE_NO_COUNTRY_REPO = 2;
    public const SLUG_BASIS_IDENTIFIER = 3;
    public const SLUG_RESTRICTIVE = 0;
    public const SLUG_PERMISSIVE = 1;
    public const SLUG_RESTRICTIVE_CHARS = '0-9a-z-';

    // From RFC 3987 IRI allowed chars. Not guaranteed to match \p{L}\p{Nd}.
    public const SLUG_RFC_3987_CHARS = "\u{00A0}-\u{D7FF}\u{F900}-\u{FDCF}"
        ."\u{FDF0}-\u{FFEF}\u{10000}-\u{1FFFD}\u{20000}-\u{2FFFD}"
        ."\u{30000}-\u{3FFFD}\u{40000}-\u{4FFFD}\u{50000}-\u{5FFFD}"
        ."\u{60000}-\u{6FFFD}\u{70000}-\u{7FFFD}\u{80000}-\u{8FFFD}"
        ."\u{90000}-\u{9FFFD}\u{A0000}-\u{AFFFD}\u{B0000}-\u{BFFFD}"
        ."\u{C0000}-\u{CFFFD}\u{D0000}-\u{DFFFD}\u{E0000}-\u{EFFFD}";

    public const SLUG_PERMISSIVE_CHARS = self::SLUG_RFC_3987_CHARS.'0-9A-Za-z-_~:;,=*@';

    public static $validSlugChars;

    public static function random($length = 12)
    {
        $separator = '-';

        // Adapted from http://stackoverflow.com/questions/5615490/random-code-generator/5615957#5615957
        $alphabet = '23456789abcdefghkmnpqrstwxyz';
        $alphabetSize = strlen($alphabet);

        $blockLength = 4;
        $numBlocks = ceil($length / $blockLength);

        $slug = '';
        for ($i = 0; $i < $numBlocks; ++$i) {
            for ($j = 0; $j < $blockLength; ++$j) {
                $slug .= $alphabet[mt_rand(0, $alphabetSize - 1)];
            }

            if ($i != $numBlocks - 1) {
                $slug .= $separator;
            }
        }

        return $slug;
    }

    /**
     * Slugify a specified string.
     *
     * @param string     $slug         The string we want to slugify
     * @param bool       $dropArticles Whether or not to drop English articles from the slug.
     *                                 We can disable this when we generate slugs by identifier.
     * @param null|mixed $creationType
     */
    public static function slugify($slug, $creationType = null)
    {
        // 0, 1, or null
        $slugCreation = (null === $creationType) ? sfConfig::get('app_permissive_slug_creation', QubitSlug::SLUG_RESTRICTIVE) : $creationType;

        // Remove apostrophes from slug
        $slug = preg_replace('/\'/', '', $slug);

        switch ($slugCreation) {
            case QubitSlug::SLUG_PERMISSIVE:
                // Whitelist - ASCII A-Za-z0-9, unicode letters, - _ ~ : ; , = * @
                $slug = preg_replace('/[^'.self::SLUG_PERMISSIVE_CHARS.']+/', '-', $slug);

                break;

            case QubitSlug::SLUG_RESTRICTIVE:
            default:
                // Handle exotic characters gracefully.
                // TRANSLIT doesn't work in musl's iconv, see #9855.
                if ((false !== $result = iconv('utf-8', 'ascii//TRANSLIT', $slug)) || (false !== $result = iconv('utf-8', 'ascii', $slug))) {
                    $slug = $result;
                }

                $slug = strtolower($slug);
                // Allow only digits, letters, and dashes.  Replace sequences of other
                // characters with dash
                $slug = preg_replace('/[^'.self::SLUG_RESTRICTIVE_CHARS.']+/', '-', $slug);
        }

        // Replace repeating dashes in slug with single dash.
        $slug = preg_replace('/-+/', '-', $slug);

        return trim($slug, '-');
    }

    public static function getUnique($connection = null)
    {
        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        // Try a max of 10 times before giving up (avoid infinite loops when
        // possible slugs exhausted)
        for ($i = 0; $i < 10; ++$i) {
            $slug = self::random();

            $statement = $connection->prepare(
                'SELECT COUNT(*)
                FROM '.QubitSlug::TABLE_NAME.'
                WHERE '.QubitSlug::SLUG.' = ?;'
            );
            $statement->execute([$slug]);

            if (0 == $statement->fetchColumn(0)) {
                return $slug;
            }
        }
    }

    public static function getByObjectId($id, array $options = [])
    {
        $criteria = new Criteria();
        $criteria->add(QubitSlug::OBJECT_ID, $id);

        if (1 == count($query = self::get($criteria, $options))) {
            return $query[0];
        }
    }

    public static function getValidSlugChars()
    {
        if (isset(self::$validSlugChars)) {
            return self::$validSlugChars;
        }

        // Default is restrictive set
        self::$validSlugChars = self::SLUG_RESTRICTIVE_CHARS;

        if (QubitSlug::SLUG_PERMISSIVE
            == QubitSetting::getByName('permissive_slug_creation')
        ) {
            self::$validSlugChars = self::SLUG_PERMISSIVE_CHARS;
        }

        return self::$validSlugChars;
    }
}
