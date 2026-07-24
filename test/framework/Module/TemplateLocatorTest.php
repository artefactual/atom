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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Tests\Framework\Module;

use Atom\Framework\Module\ModuleDirectories;
use Atom\Framework\Module\TemplateLocator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class TemplateLocatorTest extends TestCase
{
    public function testFindsApplicationTemplates(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $locator = new TemplateLocator(new ModuleDirectories(
            $projectDirectory,
            'qubit',
        ));

        self::assertSame(
            $projectDirectory
                .'/apps/qubit/modules/staticpage/templates/homeSuccess.php',
            $locator->find('staticpage', 'home'),
        );
    }

    public function testFindsPluginTemplates(): void
    {
        $projectDirectory = dirname(__DIR__, 3);
        $locator = new TemplateLocator(new ModuleDirectories(
            $projectDirectory,
            'qubit',
            ['sfIsadPlugin'],
        ));

        self::assertSame(
            $projectDirectory
                .'/plugins/sfIsadPlugin/modules/sfIsadPlugin/templates/'
                .'indexSuccess.php',
            $locator->find('sfIsadPlugin', 'index'),
        );
    }
}
