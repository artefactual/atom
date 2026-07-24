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

namespace Atom\Tests\Framework\Bridge;

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 3).'/lib/helper/QubitHelper.php';

/**
 * @internal
 *
 * @coversNothing
 */
final class QubitHelperTest extends TestCase
{
    public function testRendersIterableValuesAsAList(): void
    {
        self::assertSame(
            '<div class="col-9 p-2">'
                .'<ul class="m-0 ms-1 ps-3">'
                .'<li>First</li><li>Second</li>'
                .'</ul></div>',
            \render_b5_show_value(
                new \ArrayIterator(['First', 'Second']),
                ['renderAsIs' => true],
            ),
        );
    }
}
