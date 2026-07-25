<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

abstract class Filter
{
    public static array $filterCalled = [];

    protected ParameterHolder $parameterHolder;
    protected mixed $context;

    public function __construct($context, $parameters = [])
    {
        $this->initialize($context, $parameters);
    }

    public function initialize($context, $parameters = [])
    {
        $this->context = $context;
        $this->parameterHolder = new ParameterHolder();
        $this->parameterHolder->add((array) $parameters);

        return true;
    }

    final public function getContext()
    {
        return $this->context;
    }

    public function getParameterHolder()
    {
        return $this->parameterHolder;
    }

    public function getParameter($name, $default = null)
    {
        return $this->parameterHolder->get($name, $default);
    }

    public function hasParameter($name)
    {
        return $this->parameterHolder->has($name);
    }

    public function setParameter($name, $value)
    {
        $this->parameterHolder->set($name, $value);
    }

    protected function isFirstCall()
    {
        $class = static::class;

        if (isset(self::$filterCalled[$class])) {
            return false;
        }

        self::$filterCalled[$class] = true;

        return true;
    }
}
