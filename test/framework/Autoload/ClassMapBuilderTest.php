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

namespace Atom\Tests\Framework\Autoload;

use Atom\Framework\Autoload\ClassMapBuilder;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class ClassMapBuilderTest extends TestCase
{
    public function testIndexesDeclaredClassesCaseInsensitively(): void
    {
        vfsStream::setup('root', null, [
            'first' => [
                'Classes.php' => <<<'PHP'
                    <?php

                    class FirstFixture {}

                    namespace Example {
                        interface ContractFixture {}
                        trait BehaviorFixture {}
                        enum StateFixture {}
                    }
                    PHP,
            ],
            'second' => [
                'FirstFixture.php' => <<<'PHP'
                    <?php

                    class FirstFixture {}
                    PHP,
            ],
        ]);

        $map = (new ClassMapBuilder())->build([
            'vfs://root/first',
            'vfs://root/second',
        ]);

        self::assertSame(
            'vfs://root/second/FirstFixture.php',
            $map['firstfixture'],
        );
        self::assertSame(
            'vfs://root/first/Classes.php',
            $map['example\\contractfixture'],
        );
        self::assertSame(
            'vfs://root/first/Classes.php',
            $map['example\\behaviorfixture'],
        );
        self::assertSame(
            'vfs://root/first/Classes.php',
            $map['example\\statefixture'],
        );
    }
}
