<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Qubit
 */
class QubitTest extends TestCase
{
    public function testSafeUnserializeReturnsArraysAndScalars()
    {
        $this->assertSame(['a' => 1], Qubit::safeUnserialize(serialize(['a' => 1])));
        $this->assertSame('value', Qubit::safeUnserialize(serialize('value')));
        $this->assertFalse(Qubit::safeUnserialize(serialize(false), true));
    }

    public function testSafeUnserializeReturnsDefaultForInvalidValues()
    {
        $this->assertSame([], Qubit::safeUnserialize('not serialized', []));
        $this->assertSame([], Qubit::safeUnserialize(null, []));
        $this->assertSame([], Qubit::safeUnserialize('', []));
    }

    public function testSafeUnserializeRejectsObjects()
    {
        $this->assertSame([], Qubit::safeUnserialize(serialize(new stdClass()), []));
        $this->assertSame([], Qubit::safeUnserialize(serialize(['nested' => new stdClass()]), []));
    }

    public function testSafeUnserializeRejectsRecursiveArrays()
    {
        $this->assertSame([], Qubit::safeUnserialize('a:1:{i:0;R:1;}', []));
    }

    public function testSafeUnserializeRejectsDeeplyNestedArrays()
    {
        $value = 'value';

        for ($i = 0; $i < 101; ++$i) {
            $value = [$value];
        }

        $this->assertSame([], Qubit::safeUnserialize(serialize($value), []));
    }
}
