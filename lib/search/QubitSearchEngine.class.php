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
 * The search engine contract: everything AtoM asks of its search index.
 *
 * QubitSearch::getInstance() returns the configured implementation
 * (arElasticSearchPlugin by default). Callers outside the engine use only
 * these methods, never the engine's own client or index objects, so another
 * engine can be added by implementing them.
 *
 * $entity is the model class an index holds ("QubitInformationObject",
 * "QubitActor", "QubitTerm", ...). Queries are still built with the engine's
 * query classes, and search() still returns the engine's result set; both
 * become engine-neutral in a later change (see issue #2285).
 *
 * @author Johan Pieterse <johan@theahg.co.za>
 */
abstract class QubitSearchEngine
{
    private $dispatcher;
    private $event;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

        if (sfContext::getInstance()->getController()->inCLI()) {
            $this->event = 'command.log';
        } else {
            $this->event = 'search.log';
        }
    }

    /**
     * Index a document (an object's serialized data) in the index for its class.
     *
     * @param array  $data      Document data, including "id"
     * @param string $indexName Model class of the index
     */
    abstract public function addDocument($data, $indexName);

    /**
     * Re-index one object, and its related documents where the engine keeps them.
     *
     * @param mixed $object  Propel object
     * @param array $options Engine options
     */
    abstract public function update($object, $options = []);

    /**
     * Change some fields of an object's document.
     *
     * @param mixed $object Propel object
     * @param array $data   Fields to set
     */
    abstract public function partialUpdate($object, $data);

    /**
     * Change some fields of a document found by class and id.
     */
    abstract public function partialUpdateById(string $className, int $id, array $data);

    /**
     * Remove an object's document.
     *
     * @param mixed $object Propel object
     */
    abstract public function delete($object);

    /**
     * Rebuild the index from the database.
     *
     * @param array $options Populate options (see search:populate)
     */
    abstract public function populate($options = []);

    /**
     * Optimize (force merge) the index.
     *
     * @param array $args Engine options
     */
    abstract public function optimize($args = []);

    /**
     * Send any documents waiting in batch mode.
     */
    abstract public function flushBatch();

    /**
     * Turn indexing on or off for this process.
     */
    abstract public function enable();

    abstract public function disable();

    /**
     * Search the index of one model class.
     *
     * @param string $entity Model class of the index
     * @param mixed  $query  Query
     *
     * @return mixed The engine's result set
     */
    abstract public function search(string $entity, $query);

    /**
     * Run several searches in one request, in order.
     *
     * @param array $searches List of [model class, query] pairs
     *
     * @return array The engine's result sets, in the same order
     */
    abstract public function multiSearch(array $searches): array;

    /**
     * The stored document of one object, or null if it is not indexed.
     *
     * @param string     $entity Model class of the index
     * @param int|string $id     Object id
     *
     * @return null|array Document source
     */
    abstract public function getDocument(string $entity, $id): ?array;

    /**
     * Number of documents in the index of one model class.
     *
     * @param string $entity Model class of the index
     * @param mixed  $query  Optional query to count matches of
     */
    abstract public function count(string $entity, $query = null): int;

    /**
     * Apply a script to every document a query matches.
     *
     * @param string $entity  Model class of the index
     * @param mixed  $query   Query selecting the documents
     * @param string $script  Painless source
     * @param array  $options Engine options, e.g. ['conflicts' => 'proceed']
     *
     * @return array Response, with at least "took" and "failures"
     */
    abstract public function updateByQuery(
        string $entity,
        $query,
        string $script,
        array $options = []
    ): array;

    /**
     * Ids of every document a query matches, read in pages (no result window).
     *
     * @param string $entity Model class of the index
     * @param mixed  $query  Query
     *
     * @return array Document ids
     */
    abstract public function scrollIds(string $entity, $query): array;

    /**
     * Set some fields on many documents of one model class in one request.
     *
     * @param string $entity   Model class of the index
     * @param array  $partials Fields to set, keyed by document id
     */
    abstract public function bulkPartialUpdate(string $entity, array $partials);

    /**
     * Version of the search server.
     */
    abstract public function getVersion(): string;

    /**
     * Log a message.
     *
     * @param string $message Log message
     */
    public function log($message)
    {
        $this->dispatcher->notify(new sfEvent($this, $this->event, [$message]));
    }
}
