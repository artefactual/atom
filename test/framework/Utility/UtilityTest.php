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

namespace AccessToMemory\test\framework\Utility;

use Atom\Framework\Utility\Finder;
use Atom\Framework\Utility\Inflector;
use Atom\Framework\Utility\Pager;
use Atom\Framework\Utility\Yaml;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class UtilityTest extends TestCase
{
    public function testInflectorPreservesAtoMConventions(): void
    {
        self::assertSame(
            'InformationObject',
            Inflector::camelize('information_object'),
        );
        self::assertSame(
            'xml_http_request',
            Inflector::underscore('XMLHttpRequest'),
        );
        self::assertSame(
            'Information object',
            Inflector::humanize('information_object_id'),
        );
    }

    public function testFinderReturnsLegacyPathValues(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $files = Finder::type('file')
            ->maxdepth(0)
            ->name('arUpgradeSqlTask.class.php')
            ->in($projectDirectory.'/lib/task/migrate');
        $plugins = Finder::type('dir')
            ->maxdepth(0)
            ->relative()
            ->name('sfIsadPlugin')
            ->in($projectDirectory.'/plugins');

        self::assertSame(
            [$projectDirectory
                .'/lib/task/migrate/arUpgradeSqlTask.class.php'],
            $files,
        );
        self::assertSame(['sfIsadPlugin'], $plugins);
    }

    public function testYamlLoadsFilesAndPagerIterates(): void
    {
        $configuration = Yaml::load(
            dirname(__DIR__, 3).'/config/search.yml',
        );
        self::assertSame(
            'elasticsearch',
            $configuration['all']['server']['host'],
        );

        $pager = new class(['one', 'two', 'three']) extends Pager {
            public function __construct(private array $values)
            {
                parent::__construct('value', 2);
                $this->setNbResults(count($values));
                $this->setLastPage(2);
            }

            public function getResults(): array
            {
                return array_slice(
                    $this->values,
                    $this->getFirstIndice() - 1,
                    $this->getMaxPerPage(),
                );
            }
        };

        self::assertSame(['one', 'two'], iterator_to_array($pager));
        self::assertSame([1, 2], $pager->getLinks());
        self::assertSame(3, count($pager));
    }
}
