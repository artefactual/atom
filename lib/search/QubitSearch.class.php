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
 */
class QubitSearch
{
    protected static $instance;
    protected static $solrInstance;

    // protected function __construct() { }
    // protected function __clone() { }

    public static function getSolrInstance(array $options = [])
    {
        $configuration = ProjectConfiguration::getActive();
        if (!$configuration->isPluginEnabled('arSolrPlugin')) {
            return false;
        }

        if (!isset(self::$solrInstance)) {
            self::$solrInstance = new arSolrPlugin($options);
            //$configuration = ProjectConfiguration::getActive();
            //if ($configuration->isPluginEnabled('arSolrPlugin')) {
              //self::$solr = new arSolrPlugin($options);
            //}
        }

        return self::$solrInstance;
    }

    public static function disableSolr()
    {
        if (!isset(self::$solrInstance)) {
            self::$solrInstance = self::getSolrInstance(['initialize' => false]);
        }

        self::$solrInstance->disable();
    }

    public static function enableSolr()
    {
        self::$solrInstance = self::getSolrInstance();

        self::$solrInstance->enable();
    }

    public static function getInstance(array $options = [])
    {
        if (!isset(self::$instance)) {
            // Using arElasticSearchPlugin but other classes could be
            // implemented, for example: arSphinxSearchPlugin
            self::$instance = new arElasticSearchPlugin($options);
            //$configuration = ProjectConfiguration::getActive();
            //if ($configuration->isPluginEnabled('arSolrPlugin')) {
              //self::$solr = new arSolrPlugin($options);
            //}
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
