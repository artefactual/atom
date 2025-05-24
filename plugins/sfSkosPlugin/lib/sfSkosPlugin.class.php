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

class sfSkosPlugin
{
    // Internal EasyRdf_Graph.
    protected $graph;
    // Notify user after this number of concepts added.
    protected $notifyAfter = 100;
    // Total amount of concepts added.
    protected $total = 0;
    // QubitTaxonomy that we're using in the import.
    protected $taxonomy;
    // QubitTerm main ancestors, it could be just the root.
    protected $parent;
    // List of languages which have been seen during the import but are not
    // available in AtoM. For reporting purposes.
    protected $unsupportedLanguages = [];
    // Import errors registered.
    protected $errors = [];

    public function __construct($taxonomyId, $options = [])
    {
        $this->logger = isset($options['logger']) ? $options['logger'] : new sfNoLogger(new sfEventDispatcher());
        $this->i18n = sfContext::getInstance()->i18n;

        if (null === $this->taxonomy = QubitTaxonomy::getById($taxonomyId)) {
            throw new sfSkosPluginException($this->i18n->__('Taxonomy with ID %1% could not be found', ['%1%' => $taxonomyId]));
        }

        if (is_null($options['parentId'])) {
            $this->parent = QubitTerm::getRoot();
        } elseif (null === $this->parent = QubitTerm::getById($options['parentId'])) {
            throw new sfSkosPluginException($this->i18n->__('Term with ID %1% could not be found', ['%1%' => $options['parentId']]));
        }

        $this->graph = new EasyRdf_Graph();

        $this->languages = sfConfig::get('app_i18n_languages');
    }

    public static function import($resource, $taxonomyId, $parentId = null)
    {
        $skos = new self($taxonomyId, ['parentId' => $parentId]);
        $skos->load($resource);

        $skos->importGraph();

        return $skos;
    }

    public function load($resource)
    {
        $scheme = parse_url($resource, PHP_URL_SCHEME);
        if (!$scheme) {
            throw new sfSkosPluginException($this->i18n->__('Malformed URI.'));
        }

        $this->logger->info($this->i18n->__('Type of scheme: %1%', ['%1%' => $scheme]));
        $this->logger->info($this->i18n->__('Taxonomy: %1%', ['%1%' => $this->taxonomy->getName(['cultureFallback' => true])]));
        $this->logger->info($this->i18n->__('Term ID: %1%', ['%1%' => $this->parent->id]));

        if ('file' === $scheme) {
            $this->graph->parseFile($resource);
        } elseif ('data' === $scheme) {
            $this->graph->parse(file_get_contents($resource));
        } elseif (in_array($scheme, ['http', 'https'])) {
            $this->graph->load($resource);
        } else {
            throw new sfSkosPluginException($this->i18n->__('Unsupported scheme!'));
        }

        if ($this->graph->isEmpty()) {
            throw new sfSkosPluginException($this->i18n->__('The graph is empty.'));
        }

        $this->logger->info($this->i18n->__('The graph contains %1% concepts.', ['%1%' => count($this->graph->allOfType('skos:Concept'))]));
    }

    public function importGraph()
    {
        $concepts = $this->getRootConcepts();
        foreach ($concepts as $item) {
            if (false === $item instanceof EasyRdf_Resource) {
                $this->logger->info($this->i18n->__('Unexpected concept, type received: %1%.', ['%1%' => gettype($item)]));

                continue;
            }

            $this->addConcept($item, $this->parent->id);
        }

        // Build list of relationships using sfSkosUniqueRelations
        $relations = new sfSkosUniqueRelations();
        foreach ($this->graph->allOfType('skos:Concept') as $c) {
            foreach ($c->allResources('skos:related') as $r) {
                $relations->insert($c->get('atom:id')->getValue(), $r->get('atom:id')->getValue());
            }
        }

        foreach ($relations as $item) {
            $relation = new QubitRelation();
            $relation->typeId = QubitTerm::TERM_RELATION_ASSOCIATIVE_ID;
            $relation->subjectId = $item[0];
            $relation->objectId = $item[1];

            $relation->indexOnSave = false;
            $relation->save();
        }

        // Report error with unsupported languages
        if (0 < count($this->unsupportedLanguages)) {
            $this->errors[] = $this->i18n->__('The following languages are used in the dataset imported but not supported by AtoM: %1%', ['%1%' => implode(',', array_keys($this->unsupportedLanguages))]);
        }

        // Re-index parent term so numberOfDescendants reflects the changes
        if (QubitTerm::ROOT_ID != $this->parent->id) {
            QubitSearch::getInstance()->update($this->parent);
        }
    }

    public function hasErrors()
    {
        return 0 < count($this->errors);
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function getGraph()
    {
        return $this->graph;
    }

    /**
     * We want to harvest the graph from top to bottom.
     */
    protected function getRootConcepts()
    {
        $conceptScheme = $this->graph->get('skos:ConceptScheme', '^rdf:type');
        if (null !== $conceptScheme) {
            $topConcepts = $conceptScheme->allResources('skos:hasTopConcept');
            if (0 < count($topConcepts)) {
                return $topConcepts;
            }
        }

        return $this->graph->allOfType('skos:Concept');
    }

    protected function addConcept(EasyRdf_Resource $concept, $parentId)
    {
        $term = new QubitTerm();
        $term->parentId = $parentId;
        $term->taxonomyId = $this->taxonomy->id;

        $this->setPrefLabel($term, $concept);
        $this->setAltLabels($term, $concept);
        $this->setScopeNote($term, $concept);
        $this->setUriSourceNote($term, $concept);

        // Map dc.coverage to term.code for place terms. Hacky Hackerton was here.
        if (QubitTaxonomy::PLACE_ID === $this->taxonomy->id) {
            if (null !== $literal = $concept->getLiteral('dc:coverage')) {
                $term->code = $literal->getValue();
            }
        }

        $term->save();

        $this->notify();

        // Update graph resource with AtoM's given ID.
        $concept->set('atom:id', (int) $term->id);

        foreach ($concept->allResources('skos:narrower') as $item) {
            $this->addConcept($item, $term->id);
        }
    }

    protected function setPrefLabel(QubitTerm $term, EasyRdf_Resource $concept)
    {
        $literals = $concept->allLiterals('skos:prefLabel');
        if (1 > count($literals)) {
            return false;
        }

        foreach ($literals as $item) {
            if (false === $lang = $this->isLangSupported($item->getLang())) {
                continue;
            }

            $term->setName($item->getValue(), ['culture' => $lang]);
        }
    }

    protected function setAltLabels(QubitTerm $term, EasyRdf_Resource $concept)
    {
        $literals = $concept->allLiterals('skos:altLabel');
        if (1 > count($literals)) {
            return false;
        }

        foreach ($literals as $item) {
            if (false === $lang = $this->isLangSupported($item->getLang())) {
                continue;
            }

            $otherName = new QubitOtherName();
            $otherName->typeId = QubitTerm::ALTERNATIVE_LABEL_ID;
            $otherName->sourceCulture = $item->getLang();
            $otherName->setName($item->getValue(), ['culture' => $lang]);

            $term->otherNames[] = $otherName;
        }
    }

    protected function setScopeNote(QubitTerm $term, EasyRdf_Resource $concept)
    {
        $literals = $concept->allLiterals('skos:scopeNote');
        if (1 > count($literals)) {
            return false;
        }

        foreach ($literals as $item) {
            if (false === $lang = $this->isLangSupported($item->getLang())) {
                continue;
            }

            $note = new QubitNote();
            $note->typeId = QubitTerm::SCOPE_NOTE_ID;
            $note->sourceCulture = $item->getLang();
            $note->setContent($item->getValue(), ['culture' => $lang]);

            $term->notes[] = $note;
        }
    }

    protected function setUriSourceNote(QubitTerm $term, EasyRdf_Resource $concept)
    {
        $note = new QubitNote();
        $note->typeId = QubitTerm::SOURCE_NOTE_ID;
        $note->content = $concept->getUri();

        $term->notes[] = $note;
    }

    protected function isLangSupported($lang)
    {
        if (in_array($lang, $this->languages)) {
            return $lang;
        }

        $this->unsupportedLanguages[$lang] = true;

        return false;
    }

    protected function notify()
    {
        ++$this->total;

        if (($this->total % $this->notifyAfter) === 0) {
            $this->logger->info($this->i18n->__('A total of %1% concepts have been processed so far.', ['%1%' => $this->total]));
        }
    }
}
