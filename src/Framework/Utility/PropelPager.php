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

class PropelPager extends Pager
{
    protected $criteria;
    protected $peer_method_name = 'doSelect';
    protected $peer_count_method_name = 'doCount';

    public function __construct($class, $maxPerPage = 10)
    {
        parent::__construct($class, $maxPerPage);
        $this->criteria = new \Criteria();
    }

    public function init()
    {
        $this->resetIterator();
        $countCriteria = clone $this->criteria;
        $countCriteria->setOffset(0)->setLimit(0);
        $count = call_user_func(
            [$this->getClassPeer(), $this->getPeerCountMethod()],
            $countCriteria,
        );
        $limit = $this->getMaxRecordLimit();
        $this->setNbResults(false === $limit
            ? $count
            : min($count, $limit));
        $this->criteria->setOffset(0)->setLimit(0);

        if (0 == $this->getPage() || 0 == $this->getMaxPerPage()) {
            $this->setLastPage(0);

            return;
        }

        $this->setLastPage(
            (int) ceil($this->getNbResults() / $this->getMaxPerPage()),
        );
        $offset = ($this->getPage() - 1) * $this->getMaxPerPage();
        $this->criteria->setOffset($offset);
        $pageLimit = $this->getMaxPerPage();

        if (false !== $limit) {
            $pageLimit = min($pageLimit, max(0, $limit - $offset));
        }

        $this->criteria->setLimit($pageLimit);
    }

    public function getResults()
    {
        return call_user_func(
            [$this->getClassPeer(), $this->getPeerMethod()],
            $this->criteria,
        );
    }

    public function getPeerMethod()
    {
        return $this->peer_method_name;
    }

    public function setPeerMethod($method)
    {
        $this->peer_method_name = $method;
    }

    public function getPeerCountMethod()
    {
        return $this->peer_count_method_name;
    }

    public function setPeerCountMethod($method)
    {
        $this->peer_count_method_name = $method;
    }

    public function getClassPeer()
    {
        return constant($this->class.'::PEER');
    }

    public function getCriteria()
    {
        return $this->criteria;
    }

    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    protected function retrieveObject($offset)
    {
        $criteria = clone $this->criteria;
        $criteria->setOffset($offset - 1)->setLimit(1);
        $results = call_user_func(
            [$this->getClassPeer(), $this->getPeerMethod()],
            $criteria,
        );

        return is_array($results) ? ($results[0] ?? null) : null;
    }
}
