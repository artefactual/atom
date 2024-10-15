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

class arSolrResult
{
    protected $_hit = [];

    public function __construct($hit)
    {
        $this->_hit = $this->getStructuredDoc($hit);
    }

    /**
     * Magic function to directly access keys inside the result.
     *
     * Returns null if key does not exist
     *
     * @param string $key Key name
     *
     * @return mixed Key value
     */
    public function __get($key)
    {
        $source = $this->getData();

        return array_key_exists($key, $source) ? $source[$key] : null;
    }

    /**
     * Magic function to support isset() calls.
     *
     * @param string $key Key name
     *
     * @return bool
     */
    public function __isset($key)
    {
        $source = $this->getData();

        return array_key_exists($key, $source) && null !== $source[$key];
    }

    public function getParam($name)
    {
        if (isset($this->_hit[$name])) {
            return $this->_hit[$name];
        }

        return [];
    }

    /**
     * Test if a param from the result hit is set.
     *
     * @param string $name Param name to test
     *
     * @return bool True if the param is set, false otherwise
     */
    public function hasParam($name)
    {
        return isset($this->_hit[$name]);
    }

    /**
     * Returns the hit id.
     *
     * @return string Hit id
     */
    public function getId()
    {
        return $this->getParam('_id');
    }

    /**
     * Returns the type of the result.
     *
     * @return string Result type
     */
    public function getType()
    {
        return $this->getParam('_type');
    }

    /**
     * Returns the raw hit array.
     *
     * @return array Hit array
     */
    public function getHit()
    {
        return $this->_hit;
    }

    /**
     * Returns the version information from the hit.
     *
     * @return int|string Document version
     */
    public function getVersion()
    {
        return $this->getParam('_version');
    }

    /**
     * Sets a parameter on the hit.
     *
     * @param string $param
     * @param mixed  $value
     */
    public function setParam($param, $value)
    {
        $this->_hit[$param] = $value;
    }

    /**
     * Alias for getData.
     *
     * @return Document
     */
    public function getDocument()
    {
        return $this->getData();
    }

    public function getData()
    {
        return $this->_hit;
    }

    public function getStructuredDoc($hit)
    {
        $structuredDoc = [];
        foreach ($hit as $propertyName => $value) {
            if (str_starts_with($propertyName, 'autocomplete_')) {
                $structuredDoc[$propertyName] = $value;

                continue;
            }
            if ('_version_' === $propertyName) {
                $structuredDoc['_version'] = $value;

                continue;
            }
            if (!str_contains($propertyName, '.')) {
                $structuredDoc["_{$propertyName}"] = $value;

                continue;
            }

            $fields = explode('.', $propertyName);
            $structuredDoc['_type'] = $fields[0];
            $docRef = &$structuredDoc;
            $numFields = count($fields);
            for ($i = 1; $i < $numFields; ++$i) {
                if (!isset($docRef[$fields[$i]])) {
                    $docRef[$fields[$i]] = [];
                }
                $docRef = &$docRef[$fields[$i]];
            }
            $docRef = $value;
        }

        return $structuredDoc;
    }
}
