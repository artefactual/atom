<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Utility;

use Atom\Framework\Bridge\ParameterHolder;

abstract class Pager implements \Countable, \Iterator
{
    protected $page = 1;
    protected $maxPerPage = 0;
    protected $lastPage = 1;
    protected $nbResults = 0;
    protected $class = '';
    protected $cursor = 1;
    protected $currentMaxLink = 1;
    protected $parameterHolder;
    protected $maxRecordLimit = false;
    protected $results;
    protected $resultsCounter = 0;

    public function __construct($class, $maxPerPage = 10)
    {
        $this->setClass($class);
        $this->setMaxPerPage($maxPerPage);
        $this->parameterHolder = new ParameterHolder();
    }

    public function init() {}

    abstract public function getResults();

    public function getLinks($number = 5): array
    {
        if ($this->lastPage < 1) {
            $this->currentMaxLink = 1;

            return [];
        }

        $start = max(1, min(
            $this->page - (int) floor($number / 2),
            max(1, $this->lastPage - $number + 1),
        ));
        $links = range(
            $start,
            min($this->lastPage, $start + $number - 1),
        );
        $this->currentMaxLink = end($links) ?: 1;

        return $links;
    }

    public function getCurrentMaxLink()
    {
        return $this->currentMaxLink;
    }

    public function getMaxRecordLimit()
    {
        return $this->maxRecordLimit;
    }

    public function setMaxRecordLimit($limit)
    {
        $this->maxRecordLimit = $limit;
    }

    public function haveToPaginate(): bool
    {
        return (bool) $this->getMaxPerPage()
            && $this->getNbResults() > $this->getMaxPerPage();
    }

    public function getCursor()
    {
        return $this->cursor;
    }

    public function setCursor($position)
    {
        $this->cursor = max(1, min((int) $position, $this->nbResults));
    }

    public function getObjectByCursor($position)
    {
        $this->setCursor($position);

        return $this->getCurrent();
    }

    public function getCurrent()
    {
        return $this->retrieveObject($this->cursor);
    }

    public function getNext()
    {
        return $this->cursor + 1 > $this->nbResults
            ? null
            : $this->retrieveObject($this->cursor + 1);
    }

    public function getPrevious()
    {
        return $this->cursor - 1 < 1
            ? null
            : $this->retrieveObject($this->cursor - 1);
    }

    public function getFirstIndice(): int
    {
        return 0 === $this->page
            ? 1
            : ($this->page - 1) * $this->maxPerPage + 1;
    }

    public function getLastIndice(): int
    {
        return 0 === $this->page
            ? $this->nbResults
            : min($this->page * $this->maxPerPage, $this->nbResults);
    }

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {
        $this->class = $class;
    }

    public function getNbResults()
    {
        return $this->nbResults;
    }

    public function getFirstPage(): int
    {
        return 1;
    }

    public function getLastPage()
    {
        return $this->lastPage;
    }

    public function getPage()
    {
        return $this->page;
    }

    public function getNextPage()
    {
        return min($this->page + 1, $this->lastPage);
    }

    public function getPreviousPage()
    {
        return max($this->page - 1, 1);
    }

    public function setPage($page)
    {
        $this->page = (int) $page;

        if ($this->page <= 0) {
            $this->page = $this->maxPerPage ? 1 : 0;
        }
    }

    public function getMaxPerPage()
    {
        return $this->maxPerPage;
    }

    public function setMaxPerPage($maximum)
    {
        if ($maximum > 0) {
            $this->maxPerPage = $maximum;
            $this->page = max(1, $this->page);
        } elseif (0 == $maximum) {
            $this->maxPerPage = 0;
            $this->page = 0;
        } else {
            $this->maxPerPage = 1;
            $this->page = max(1, $this->page);
        }
    }

    public function isFirstPage(): bool
    {
        return 1 === $this->page;
    }

    public function isLastPage(): bool
    {
        return $this->page == $this->lastPage;
    }

    public function getParameterHolder(): ParameterHolder
    {
        return $this->parameterHolder;
    }

    public function getParameter($name, $default = null)
    {
        return $this->parameterHolder->get($name, $default);
    }

    public function hasParameter($name): bool
    {
        return $this->parameterHolder->has($name);
    }

    public function setParameter($name, $value)
    {
        $this->parameterHolder->set($name, $value);
    }

    #[\ReturnTypeWillChange]
    public function current()
    {
        $this->results ?? $this->initializeIterator();

        return current($this->results);
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        $this->results ?? $this->initializeIterator();

        return key($this->results);
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->results ?? $this->initializeIterator();
        --$this->resultsCounter;

        return next($this->results);
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        $this->results ?? $this->initializeIterator();
        $this->resultsCounter = count($this->results);

        return reset($this->results);
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        $this->results ?? $this->initializeIterator();

        return $this->resultsCounter > 0;
    }

    #[\ReturnTypeWillChange]
    public function count()
    {
        return $this->getNbResults();
    }

    protected function retrieveObject($offset)
    {
        $results = $this->getResults();

        return $results[$offset - 1] ?? null;
    }

    protected function setNbResults($number)
    {
        $this->nbResults = $number;
    }

    protected function setLastPage($page)
    {
        $this->lastPage = $page;

        if ($this->page > $page) {
            $this->setPage($page);
        }
    }

    protected function initializeIterator(): void
    {
        $this->results = $this->getResults();
        $this->resultsCounter = count($this->results);
    }

    protected function resetIterator(): void
    {
        $this->results = null;
        $this->resultsCounter = 0;
    }
}
