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

class QubitI18N
{
    /**
     * Similar to sfI18N::__($string) but returning a dictionary with all the
     * translations available indexed by their language codes. Untranslated
     * messages are omitted.
     *
     * This function is probably very slow to be used in the
     * request/response cycle but it is probably okay to use it during the
     * execution of a CLI task or during the installation.
     *
     * @param mixed $string
     */
    public static function getTranslations($string)
    {
        $translations = self::getTranslationsForStrings([$string]);

        return $translations[$string];
    }

    /**
     * Return translations for several source strings in one pass over the
     * configured application languages. This keeps getTranslations() compatible
     * while allowing install tasks to avoid rebuilding i18n state for each
     * string.
     */
    public static function getTranslationsForStrings(array $strings)
    {
        $translations = [];
        foreach ($strings as $string) {
            $translations[$string] = [];
        }

        // Index the language codes available in the application.
        $languageCodes = [];
        foreach (new DirectoryIterator(sfConfig::get('sf_app_i18n_dir')) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }

            $languageCodes[] = $fileInfo->getBasename();
        }

        $configuration = sfContext::getInstance()->getConfiguration();
        foreach ($languageCodes as $langCode) {
            $i18n = new sfI18N($configuration, new sfNoCache(), ['culture' => $langCode]);

            // Mark untranslated messages so they can be omitted below.
            $i18n->getMessageFormat()->setUntranslatedPS(['[T]', '[/T]']);

            foreach ($strings as $string) {
                $value = $i18n->__($string);

                // Discard empty and untranslated messages.
                if (!empty($value) && 0 !== strpos($value, '[T]', 0)) {
                    $translations[$string][$langCode] = $value;
                }
            }
        }

        return $translations;
    }
}
