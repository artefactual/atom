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
 * arSolr.
 */
class arSolrQueryString extends arSolrQuery
{
    /**
     * Query string.
     *
     * @var string Query string
     */
    protected $_queryString;

    /**
     * Creates query string object. Calls setQuery with argument.
     *
     * @param string $queryString OPTIONAL Query string for object
     */
    public function __construct(string $queryString = '')
    {
        $this->setQuery($queryString);
    }

    /**
     * Sets the default field.
     * You cannot set fields and default_field.
     *
     * If no field is set, _all is chosen
     *
     * @param string $field Field
     *
     * @return $this
     */
    public function setDefaultField(string $field): self
    {
        return $this->setParam('default_field', $field);
    }

    /**
     * Sets the default operator AND or OR.
     *
     * If no operator is set, OR is chosen
     *
     * @param string $operator Operator
     *
     * @return $this
     */
    public function setQueryDefaultOperator(string $operator = 'or'): self
    {
        return $this->setParam('default_operator', $operator);
    }

    /**
     * Sets the analyzer to analyze the query with.
     *
     * @param string $analyzer Analyser to use
     *
     * @return $this
     */
    public function setAnalyzer(string $analyzer): self
    {
        return $this->setParam('analyzer', $analyzer);
    }

    /**
     * Sets the parameter to allow * and ? as first characters.
     *
     * If not set, defaults to true.
     *
     * @return $this
     */
    public function setAllowLeadingWildcard(bool $allow = true): self
    {
        return $this->setParam('allow_leading_wildcard', $allow);
    }

    /**
     * Sets the parameter to enable the position increments in result queries.
     *
     * If not set, defaults to true.
     *
     * @return $this
     */
    public function setEnablePositionIncrements(bool $enabled = true): self
    {
        return $this->setParam('enable_position_increments', $enabled);
    }

    /**
     * Sets the fuzzy prefix length parameter.
     *
     * If not set, defaults to 0.
     *
     * @return $this
     */
    public function setFuzzyPrefixLength(int $length = 0): self
    {
        return $this->setParam('fuzzy_prefix_length', $length);
    }

    /**
     * Sets the fuzzy minimal similarity parameter.
     *
     * If not set, defaults to 0.5
     *
     * @return $this
     */
    public function setFuzzyMinSim(float $minSim = 0.5): self
    {
        return $this->setParam('fuzzy_min_sim', $minSim);
    }

    /**
     * Sets the phrase slop.
     *
     * If zero, exact phrases are required.
     * If not set, defaults to zero.
     *
     * @return $this
     */
    public function setPhraseSlop(int $phraseSlop = 0): self
    {
        return $this->setParam('phrase_slop', $phraseSlop);
    }

    /**
     * Sets the boost value of the query.
     *
     * If not set, defaults to 1.0.
     *
     * @return $this
     */
    public function setBoost(float $boost = 1.0): self
    {
        return $this->setParam('boost', $boost);
    }

    /**
     * Allows analyzing of wildcard terms.
     *
     * If not set, defaults to true
     *
     * @return $this
     */
    public function setAnalyzeWildcard(bool $analyze = true): self
    {
        return $this->setParam('analyze_wildcard', $analyze);
    }

    /**
     * Sets the fields. If no fields are set, _all is chosen.
     * You cannot set fields and default_field.
     *
     * @param array $fields Fields
     *
     * @return $this
     */
    public function setQueryFields(array $fields): self
    {
        return $this->setParam('fields', $fields);
    }

    /**
     * Whether to use bool or dis_max queries to internally combine results for multi field search.
     *
     * @param bool $value Determines whether to use
     *
     * @return $this
     */
    public function setUseDisMax(bool $value = true): self
    {
        return $this->setParam('use_dis_max', $value);
    }

    /**
     * When using dis_max, the disjunction max tie breaker.
     *
     * If not set, defaults to 0.0.
     *
     * @return $this
     */
    public function setTieBreaker(float $tieBreaker = 0.0): self
    {
        return $this->setParam('tie_breaker', $tieBreaker);
    }

    /**
     * Set a re-write condition. See https://github.com/elasticsearch/elasticsearch/issues/1186 for additional information.
     *
     * @return $this
     */
    public function setRewrite(string $rewrite = ''): self
    {
        return $this->setParam('rewrite', $rewrite);
    }

    /**
     * Set timezone option.
     *
     * @return $this
     */
    public function setTimezone(string $timezone): self
    {
        return $this->setParam('time_zone', $timezone);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return ['query_string' => \array_merge(['query' => $this->_queryString], $this->getParams())];
    }
}
