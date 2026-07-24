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

namespace Atom\Framework\Configuration;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\ParameterHolder;
use Atom\Framework\Utility\Toolkit;

abstract class ConfigHandler
{
    protected $parameterHolder;

    public function __construct($parameters = null)
    {
        $this->initialize($parameters);
    }

    public function initialize($parameters = null)
    {
        $this->parameterHolder = new ParameterHolder();
        $this->parameterHolder->add($parameters);
    }

    public static function replaceConstants(mixed $value): mixed
    {
        return (new ConstantReplacer())->replace(
            $value,
            Configuration::getAll(),
        );
    }

    public static function replacePath(mixed $path): mixed
    {
        if (is_array($path)) {
            return array_map(self::replacePath(...), $path);
        }

        if (!Toolkit::isPathAbsolute($path)) {
            return rtrim(
                (string) Configuration::get('sf_app_dir'),
                '/\\',
            ).'/'.ltrim((string) $path, '/\\');
        }

        return $path;
    }

    public function getParameterHolder(): ParameterHolder
    {
        return $this->parameterHolder;
    }

    abstract public function execute($configFiles);
}
