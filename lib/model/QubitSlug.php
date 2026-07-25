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

    // Permissive-mode routing character set: used by getValidSlugChars() to build
    // router regexes like `[<chars>]+`. Intentionally a superset of what slugify()
    // generates in permissive mode so legacy slugs continue to route. slugify()
    // remains authoritative and applies Unicode-property filtering plus
    // pre/post normalization (e.g., removing Cf/Cc/Z* and mapping Pd->"-"). Do not
    // try to encode slugify()’s full logic here; sfRoute does not compile with /u.
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
     * Unicode class legend used in regexes (with /u):
     * - \p{L}: Letters (all scripts)
     * - \p{N}: Numbers (decimal/letter/other)
     * - \p{M}: Combining marks (diacritics)
     * - \p{Zs}: Space separators; \p{Zl}/\p{Zp}: line/paragraph separators
     * - \p{Pd}: Dash punctuation (e.g., – — − ‑) covering dash types
     * - \p{Cf}: Format controls (e.g., ZWJ/ZWNJ, soft hyphen, bi-directional marks)
     * - \p{Cc}: Control characters (ASCII control range)
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

        // Normalize input and remove literal apostrophes quickly (iconv may add more later)
        $slug = (string) $slug; // ensure string
        $slug = str_replace("'", '', $slug); // fast apostrophe removal

        // ASCII fast-path to avoid Unicode regex/iconv when not needed
        $isAscii = ('' === $slug || 1 === preg_match('/^[\x00-\x7F]+$/', $slug));

        switch ($slugCreation) {
            case QubitSlug::SLUG_PERMISSIVE:
                if ($isAscii) {
                    // ASCII allowlist: letters/digits and safe ASCII - _ ~ : ; , = * @; others become "-"
                    $slug = preg_replace('/[^A-Za-z0-9\-_:;,=\*@~]+/', '-', $slug);

                    break;
                }

                // Remove controls/formatting, line/paragraph separators, and spaces and replace with single dash.
                $slug = preg_replace('/[\p{Cf}\p{Cc}\p{Zl}\p{Zp}\p{Zs}]+/u', '-', $slug); // strip Cf/Cc/Zl/Zp/Zs
                // Normalize any Unicode dash punctuation to ASCII hyphen-minus
                $slug = preg_replace('/\p{Pd}+/u', '-', $slug); // map Pd to "-"

                // Allow Unicode letters/digits plus safe ASCII: - _ ~ : ; , = * @
                $slug = preg_replace('/[^\p{L}\p{N}\-_:;,=\*@~]+/u', '-', $slug); // property-based allowlist

                break;

            case QubitSlug::SLUG_RESTRICTIVE:
            default:
                if ($isAscii) {
                    // ASCII: lowercase then filter to [0-9a-z-]
                    $slug = strtolower($slug);
                    $slug = preg_replace('/[^'.self::SLUG_RESTRICTIVE_CHARS.']+/', '-', $slug);

                    break;
                }

                // Explicitly drop common format controls that may behave inconsistently across envs
                // (ZWNJ U+200C, ZWJ U+200D, soft hyphen U+00AD, VS15 U+FE0E, VS16 U+FE0F)
                $slug = str_replace(["\u{200C}", "\u{200D}", "\u{00AD}", "\u{FE0E}", "\u{FE0F}"], '', $slug); // remove specific Cf
                // Also remove any remaining format controls via Unicode property
                $slug = preg_replace('/\p{Cf}+/u', '', $slug); // remove other format controls

                // Before transliteration, drop symbols/pictographs and most punctuation
                // to avoid iconv expanding them to ASCII letters/digits (e.g., € -> EUR, • -> o, ° -> 0).
                // Keep letters, marks, numbers, spaces, and hyphen; turn other runs into dashes.
                $slug = preg_replace('/[^\p{L}\p{M}\p{N}\p{Zs}-]+/u', '-', $slug);

                if (false !== $result = iconv('utf-8', 'ascii//TRANSLIT//IGNORE', $slug)) {
                    $slug = $result;
                }

                // iconv may introduce ASCII diacritic markers (e.g., 'e, "u, ^a, `a, ~n).
                // Strip them before filtering to avoid spurious dashes.
                $slug = str_replace(["'", '"', '`', '^', '~'], '', $slug);

                $slug = strtolower($slug);
                // Allow only digits, letters, and dashes.  Replace sequences of other
                // characters with dash.
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

    /**
     * Returns a character class for building route regexes that match slugs.
     *
     * - Restrictive mode returns an exact set matching slugify(): '0-9a-z-'.
     * - Permissive mode returns an IRI-inspired superset so older/legacy slugs
     *   continue to match routes. slugify() may generate a stricter subset
     *   because it removes default-ignorables/controls and normalizes dashes.
     * - This function is for routing only; slugify() is authoritative for
     *   slug generation rules and normalization.
     */
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
