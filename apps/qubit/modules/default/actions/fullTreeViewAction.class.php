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
 * Action Handler for FullWidth TreeView.
 *
 * @author Andy Koch <koch.andy@gmail.com>
 * @author David Juhasz <david@artefactual.com>
 */
class DefaultFullTreeViewAction extends sfAction
{
    protected $ancestorIds = [];
    protected $lodNames = [];

    public function execute($request)
    {
        ProjectConfiguration::getActive()->loadHelpers('Qubit');

        // Get show identifier setting to prepare the reference code if necessary
        $this->showIdentifier = sfConfig::get('app_treeview_show_identifier', 'no');

        // Set current user culture
        $this->culture = $this->getUser()->getCulture();

        // Set response to JSON
        $this->getResponse()->setContentType('application/json');
    }

    /**
     * Get treeview data for the children of the given information object.
     *
     * @param int   $informationObjectId information object id
     * @param array $options             optional arguments
     *
     * @return array treeview data
     */
    public function getChildren($informationObjectId, $options = [])
    {
        // Get child data
        $results = $this->findChildren($informationObjectId, $options);

        return $this->formatResultsData($results, $options);
    }

    /**
     * Get treeview data for ancestors and siblings of $this->resource.
     *
     * @param array $options optional arguments
     *
     * @return array treeview data
     */
    public function getAncestorsAndSiblings($options = [])
    {
        $this->ancestorIds = $this->getAncestorIds($this->resource->id);
        $this->collectionRootId = $this->getCollectionRootId();

        // Get the collection root data (don't get it's siblings)
        $result = $this->getElasticSearchResult($this->collectionRootId, $options);

        // Format collection root data for the treeview, and recursively add its
        // children until we get to the $this->resource and it's siblings
        $data = $this->formatResultData($result, $options + ['recursive' => true]);

        return ['nodes' => [$data], 'total' => 1];
    }

    /**
     * Get an array of ids for the ancestors of the given information object.
     *
     * @param int $informationObjectId information object id
     *
     * @return array ancestor ids
     */
    protected function getAncestorIds($informationObjectId)
    {
        $result = $this->getElasticSearchResult($informationObjectId);

        if (null === $result) {
            return null;
        }

        $data = $result->getData();

        return $data['ancestors'];
    }

    /**
     * Get the collection root for this $this->resource.
     *
     * The collection root is first child of the information object root node
     * which is either an ancestor of $this->resource, or $this->resource itself
     * if it's a child of the root node
     *
     * @return null|int collection root id; null if no collection root found
     */
    protected function getCollectionRootId()
    {
        // If $this->ancestorIds is empty, something went wrong
        if (empty($this->ancestorIds)) {
            return null;
        }

        // If the parent of $this->resource is the root node, then this resource is
        // the collection root
        if ($this->ancestorIds == [QubitInformationObject::ROOT_ID]) {
            return $this->resource->id;
        }

        // Otherwise the second ancestor is the collection root
        return $this->ancestorIds[1];
    }

    /**
     * Get Elasticsearch query object for the given $term.
     *
     * @param \Elastica\Query\Term $term    query term
     * @param array                $options optional arguments
     *
     * @return \Elastica\Query query object
     */
    protected function getElasticSearchQuery($term, $options = [])
    {
        // Initialize Elasticsearch query
        $query = new arElasticSearchPluginQuery(
            $this->getPageLimit($options),
            $this->getPageSkip($options)
        );

        // Add search term
        $query->queryBool->addMust($term);

        // Filter drafts
        if (!$this->getUser()->isAuthenticated()) {
            $query->queryBool->addMust(
                new \Elastica\Query\Term(
                    ['publicationStatusId' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID]
                )
            );
        }

        return $query;
    }

    /**
     * Do Elasticsearch query for given $term.
     *
     * @param \Elastica\Query\Term $term    query term
     * @param array                $options optional arguments
     *
     * @return \Elastica\ResultSet search result set
     */
    protected function doElasticsearchQuery($term, $options = [])
    {
        $query = $this->getElasticSearchQuery($term, $options);

        // Set sort order
        $this->setSortOrder($query, $options);

        // Get results, with drafts filtered when appropriate
        return QubitSearch::getInstance()
            ->index
            ->getIndex('QubitInformationObject')
            ->search($query->getQuery(false, false));
    }

    /**
     * Get Elasticsearch data for information object.
     *
     * @param int   $id      information_object id
     * @param array $options optional arguments
     *
     * @return \Elastica\Result Elasticsearch search result
     */
    protected function getElasticSearchResult($id, $options = [])
    {
        // Get resultset, with drafts filtered when appropriate
        $results = $this->doElasticsearchQuery(
            new \Elastica\Query\Term(['_id' => $id]),
            $options
        );

        // If the information object is a draft, no results are returned
        if (0 === $results->count()) {
            return null;
        }

        // There should only be one result in the resultset
        return $results->current();
    }

    /**
     * Find children of information object.
     *
     * @param int   $id      information_object.id (pkey)
     * @param array $options optional arguments
     *
     * @return \Elastica\ResultSet search result set
     */
    protected function findChildren($id, $options = [])
    {
        $term = new \Elastica\Query\Term(['parentId' => $id]);

        return $this->doElasticsearchQuery($term, $options);
    }

    /**
     * Return a count of an information object's children.
     *
     * @param int   $id      information_object.id (pkey)
     * @param array $options optional arguments
     *
     * @return int number of children
     */
    protected function countChildren($id, $options = [])
    {
        $term = new \Elastica\Query\Term(['parentId' => $id]);
        $options['limit'] = 0;

        $query = $this->getElasticSearchQuery($term, $options);

        // Return a count of the results found
        return QubitSearch::getInstance()
            ->index
            ->getIndex('QubitInformationObject')
            ->count($query->getQuery(false, false));
    }

    /**
     * Format Elasticsearch results data for treeview javascript.
     *
     * @param \Elastica\ResultSet $results Elasticsearch results
     * @param array               $options optional arguments
     *
     * @return array treeview data
     */
    protected function formatResultsData($results, $options)
    {
        $data = [];

        foreach ($results as $result) {
            // Append node to $data array
            $data[] = $this->formatResultData($result, $options);
        }

        // If $option['memorySort'] is true, order data in-memory by "text"
        // attribute of each node
        if (isset($options['memorySort']) && $options['memorySort']) {
            $this->memorySort($data);
        }

        return ['nodes' => $data, 'total' => $results->getTotalHits()];
    }

    /**
     * Format Elasticsearch result data for use in treeview javasccript.
     *
     * @param Elastica\Result $result  Elasticsearch search result
     * @param array           $options optional arguments
     *
     * @return array formatted data for treeview node
     */
    protected function formatResultData($result, $options)
    {
        // Get Elasticsearch search result data as an array
        $data = $result->getData();

        $node = [];
        $node['id'] = $result->getId();
        $node['text'] = render_value_inline($this->getNodeText($data));

        // Set some special flags on our currently selected node
        if ($result->getId() == $this->resource->id) {
            $node['state'] = ['opened' => true, 'selected' => true];
            $node['li_attr'] = ['selected_on_load' => true];
        }

        if (!empty($data['levelOfDescriptionId'])) {
            $lod = $data['levelOfDescriptionId'];

            // Cache English LOD names
            if (empty($this->lodNames[$lod]) && null !== $lodTerm = QubitTerm::getById($lod)) {
                $this->lodNames[$lod] = $lodTerm->getName(['sourceCulture' => true]);
            }

            // Set type as LOD so specific icons can be used
            if (!empty($this->lodNames[$lod])) {
                $node['type'] = $this->lodNames[$lod];
            }
        }

        // Add <a> element attributes
        $node['a_attr']['title'] = strip_tags($node['text']);
        $node['a_attr']['href'] = $this->generateUrl(
            'slug',
            ['slug' => $data['slug']]
        );

        // If node has children
        if ($this->countChildren($node['id']) > 0) {
            // Set children to default of true for lazy loading
            $node['children'] = true;

            // If this node is an ancestor of the target node, add child data
            if (
                isset($options['recursive']) && $options['recursive']
                && in_array($node['id'], $this->ancestorIds)
            ) {
                $children = $this->getChildren($node['id'], $options);

                $node['children'] = $children['nodes'];
                $node['total'] = $children['total'];
            }
        }

        return $node;
    }

    /**
     * Get number of results to show per "page".
     *
     * @param array $options optional query parameters
     *
     * @return int maximum number of results to return
     */
    protected function getPageLimit($options)
    {
        // Get default limit from config
        $limit = sfConfig::get('app_treeview_full_items_per_page', 8000);

        if (isset($options['limit']) && intval($options['limit']) > 0) {
            $limit = intval($options['limit']);

            // Don't allow a limit greater than the max value
            if ($limit > sfConfig::get('app_treeview_items_per_page_max', 10000)) {
                $limit = sfConfig::get('app_treeview_items_per_page_max', 10000);
            }
        }

        return $limit;
    }

    /**
     * Get number of results to skip to show current "page" of results.
     *
     * @param array $options optional query parameters
     *
     * @return int number of results to skip (default: 0)
     */
    protected function getPageSkip($options)
    {
        $skip = 0;

        if (isset($options['skip']) && intval($options['skip']) > 0) {
            $skip = intval($options['skip']);
        }

        return $skip;
    }

    /**
     * Set sort order for Elasticsearch query.
     *
     * @param arElasticSearchPluginQuery $query   Elasticsearch query object
     * @param array                      $options optional arguments
     */
    protected function setSortOrder(&$query, $options)
    {
        // Default: sort by "lft"
        $sortField = 'lft';

        // Define allowed sort fields
        $allowedSorts = [
            'title' => sprintf('i18n.%s.title.alphasort', $this->culture),
        ];

        if (
            isset($options['orderColumn'])
            && in_array($options['orderColumn'], $allowedSorts)
        ) {
            $sortField = $options['orderColumn'];
        }

        $query->query->addSort([$sortField => 'asc']);
    }

    /**
     * Get the display text for a treeview node.
     *
     * In addition to the description title, the dispay text may include
     * the description identifier, reference code, level of description, event
     * dates, and a "Draft" indicator
     *
     * @param array $record archival description data
     *
     * @return string the display text for a treeview record
     */
    protected function getNodeText($record)
    {
        $text = $this->getTitle($record);

        // Prepend identifier or reference code to text, based on settings
        $text = $this->addIdentifier($text, $record);

        // Prepend level of description, based on settings
        $text = $this->addLevelOfDescription($text, $record);

        // Append dates, based on settings
        $text = $this->addDates($text, $record);

        // Prepend "(Draft) " to draft descriptions
        return $this->addDraftText($text, $record);
    }

    /**
     * Get the title of a description in the best available culture.
     *
     * Return description title in the current culture if available, and if not
     * then fall back to the source culture title.  If the description has *no*
     * valid title, then return "<em>Untitled</em>"
     *
     * @param array $record archival description data
     *
     * @return string the best available archival description title
     */
    protected function getTitle($record)
    {
        return get_search_i18n(
            $record,
            'title',
            ['allowEmpty' => false, 'culture' => $this->culture]
        );
    }

    /**
     * Prepend an identifier or reference code based on the application "full
     * width treeview > show identifier" setting.
     *
     * @param array $text   input text
     * @param array $record information object data
     *
     * @return string the updated text
     */
    protected function addIdentifier($text, $record)
    {
        $identifer = null;

        // If show identifier setting is "no" return the input text unaltered
        if ('no' === $this->showIdentifier) {
            return $text;
        }

        // If show identifier setting is "identifier", use the local identifier
        if ('identifier' == $this->showIdentifier && isset($record['identifier'])) {
            $identifier = $record['identifier'];
        }

        // If show identifier setting is "reference code", use it
        if (
            'referenceCode' === $this->showIdentifier
            && isset($record['referenceCode'])
        ) {
            $identifier = $record['referenceCode'];
        }

        // If $identifier has a value, prepend it to the input text
        if (!empty($identifier)) {
            return sprintf('%s - %s', $identifier, $text);
        }

        return $text;
    }

    /**
     * Prepend level of description text when appropriate.
     *
     * @param string $text   input text
     * @param array  $record information object data
     *
     * @return string updated text
     */
    protected function addLevelOfDescription($text, $record)
    {
        if (
            'yes' === sfConfig::get('app_treeview_show_level_of_description', 'yes')
            && !empty($record['levelOfDescriptionId'])
        ) {
            return sprintf(
                '[%s] %s',
                render_value_inline(
                    QubitCache::getLabel($record['levelOfDescriptionId'], 'QubitTerm')
                ),
                $text
            );
        }

        return $text;
    }

    /**
     * Append dates when appropriate.
     *
     * @param string $text   input text
     * @param array  $record information object data
     *
     * @return string updated text
     */
    protected function addDates($text, $record)
    {
        $dates = '';

        // Check that treeview dates setting is "yes"
        if (
            'yes' === sfConfig::get('app_treeview_show_dates', 'no')
            && isset($record['dates'])
        ) {
            $dates = render_search_result_date($record['dates']);
        }

        // Append dates to text
        if (!empty($dates)) {
            $text .= ", {$dates}";
        }

        return $text;
    }

    /**
     * Prepend "(Draft)" to draft descriptions.
     *
     * @param string $text   input text
     * @param array  $record information object data
     *
     * @return string updated text
     */
    protected function addDraftText($text, $record)
    {
        if (
            isset($record['publicationStatusId'])
            && QubitTerm::PUBLICATION_STATUS_DRAFT_ID == $record['publicationStatusId']
        ) {
            // Prepend "(Draft) " to draft records
            $text = sprintf(
                '(%s) %s',
                QubitCache::getLabel($record['publicationStatusId'], 'QubitTerm'),
                $text
            );
        }

        return $text;
    }

    /**
     * Sort data in-memory by "text" attribute of each node.
     *
     * @param array $data data to sort
     */
    protected function memorySort(&$data)
    {
        $titles = [];

        foreach ($data as $key => $node) {
            $titles[$key] = $node['text'];
        }

        usort($data, function ($el1, $el2) {
            return strnatcmp($el1['text'], $el2['text']);
        });
    }
}
