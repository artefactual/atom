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

class arSolrResultSet implements ArrayAccess, Countable, Iterator
{
    /**
     * Response.
     *
     * @var null|array
     */
    protected $_response;

    /**
     * Current position.
     *
     * @var int Current position
     */
    private $_position = 0;

    /**
     * Results.
     *
     * @var Result[] Results
     */
    private $_results = [];

    /**
     * Constructs ResultSet object.
     *
     * @param Response $response Response object
     * @param Query    $query    Query object
     * @param Result[] $results
     */
    public function __construct($response)
    {
        $this->_response = $response->response;
        $this->_results = $response->response->docs;
    }

    public function getResults()
    {
        return $this->_results;
    }

    public function getTotalHits()
    {
        return isset($this->_response['numFound']) ? (int) $this->_response['numFound'] : 0;
    }

    public function getMaxScore()
    {
        return isset($this->_response['maxScore']) ? (float) $this->_response['maxScore'] : 0;
    }

    public function getDocument($doc) {
        $structuredDoc = [];
        foreach ($doc as $propertyName => $value) {
            if (!str_contains($propertyName, '.')) {
                // Skip solr ID and version fields
                break;
            }

            $fields = explode('.', $propertyName);
            $structuredDoc['type'] = $fields[0];
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

    public function getDocuments()
    {
        $documents = [];
        foreach ($this->_results as $doc) {
            $documents[] = $this->getDocument($doc);
        }

        return $documents;
    }

    /**
     * Returns size of current set.
     *
     * @return int Size of set
     */
    public function count()
    {
        return count($this->_results);
    }

    /**
     * Returns the current object of the set.
     *
     * @return \Elastica\Result|false Set object or false if not valid (no more entries)
     */
    public function current()
    {
        if ($this->valid()) {
            return $this->_results[$this->key()];
        }

        return false;
    }

    /**
     * Sets pointer (current) to the next item of the set.
     */
    public function next()
    {
        ++$this->_position;

        return $this->current();
    }

    /**
     * Returns the position of the current entry.
     *
     * @return int Current position
     */
    public function key()
    {
        return $this->_position;
    }

    /**
     * Check if an object exists at the current position.
     *
     * @return bool True if object exists
     */
    public function valid()
    {
        return isset($this->_results[$this->key()]);
    }

    /**
     * Resets position to 0, restarts iterator.
     */
    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * Whether a offset exists.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param int $offset
     *
     * @return bool true on success or false on failure
     */
    public function offsetExists($offset)
    {
        return isset($this->_results[$offset]);
    }

    /**
     * Offset to retrieve.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param int $offset
     *
     * @throws Exception\InvalidException If offset doesn't exist
     *
     * @return null|Result
     */
    public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->_results[$offset];
        }

        throw new InvalidException('Offset does not exist.');
    }

    /**
     * Offset to set.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param int    $offset
     * @param Result $value
     *
     * @throws Exception\InvalidException
     */
    public function offsetSet($offset, $value)
    {
        if (!($value instanceof Result)) {
            throw new InvalidException('ResultSet is a collection of Result only.');
        }

        if (!isset($this->_results[$offset])) {
            throw new InvalidException('Offset does not exist.');
        }

        $this->_results[$offset] = $value;
    }

    /**
     * Offset to unset.
     *
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->_results[$offset]);
    }
}
