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
 * Singleton factory class for QubitSearchEngine and subclasses.
 *
 * @author Johan Pieterse <johan@theahg.co.za>
 */
class QubitSearch
{
    protected static $instance;

    // protected function __construct() { }
    // protected function __clone() { }

    public static function getInstance(array $options = [])
    {
        if (!isset(self::$instance)) {
            // The engine is any QubitSearchEngine subclass; arElasticSearchPlugin
            // unless the app_search_engine setting names another.
            $engine = sfConfig::get('app_search_engine', 'arElasticSearchPlugin');
            if (!is_subclass_of($engine, 'QubitSearchEngine')) {
                throw new sfException(sprintf('Search engine "%s" is not a QubitSearchEngine.', $engine));
            }

            self::$instance = new $engine($options);
        }

        return self::$instance;
    }

    public static function disable()
    {
        if (!isset(self::$instance)) {
            self::$instance = self::getInstance(['initialize' => false]);
        }

        self::$instance->disable();
    }

    public static function enable()
    {
        self::$instance = self::getInstance();

        self::$instance->enable();
    }
}
