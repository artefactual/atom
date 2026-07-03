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

class QubitCultureHeaderFilter extends sfFilter
{
    protected static $allowedCultures;

    public function execute($filterChain)
    {
        // Allow reverse proxies to pass a header to change culture
        if (!empty($_SERVER['HTTP_X_ATOM_CULTURE'])) {
            $culture = trim($_SERVER['HTTP_X_ATOM_CULTURE']);

            if ($this->isAllowedCulture($culture)) {
                sfContext::getInstance()->getUser()->setCulture($culture);
            }
        }

        $filterChain->execute();
    }

    protected function isAllowedCulture($culture)
    {
        if (!is_string($culture) || '' === $culture) {
            return false;
        }

        if (null === self::$allowedCultures) {
            self::$allowedCultures = array_fill_keys(
                sfConfig::get('app_i18n_languages', []),
                true
            );
        }

        return isset(self::$allowedCultures[$culture]);
    }
}
