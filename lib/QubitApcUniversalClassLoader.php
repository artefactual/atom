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

use Symfony\Component\ClassLoader\UniversalClassLoader;

/**
 * QubicApcUniversalClassLoader implements a "universal" autoloader cached in APCu/APC.
 *
 * Based on ApcUniversalClassLoader.
 *
 * @author Mike Cantelon <mike@artefactual.com>
 *
 */
class QubitApcUniversalClassLoader extends UniversalClassLoader
{
    private $prefix;

    /**
     * Constructor.
     *
     * @param string $prefix A prefix to create a namespace in APCu/APC
     *
     * @throws \RuntimeException
     */
    public function __construct($prefix)
    {
        if (!extension_loaded('apcu') && !extension_loaded('apc')) {
            throw new \RuntimeException('Unable to use QubitApcUniversalClassLoader as neither APCu or APC are enabled.');
        }

        $this->prefix = $prefix;
    }

    /**
     * Finds a file by class name while caching lookups to APCu/APC.
     *
     * @param string $class A class name to resolve to file
     *
     * @return string|null The path, if found
     */
    public function findFile($class)
    {
        $functionPrefix = (extension_loaded('apcu')) ? 'apcu' : 'apc';
        $fetchFunction = $functionPrefix .'_fetch';
        $storeFunction = $functionPrefix .'_store';

        if (false === $file = $fetchFunction($this->prefix.$class)) {
            $storeFunction($this->prefix.$class, $file = parent::findFile($class));
        }

        return $file;
    }
}
