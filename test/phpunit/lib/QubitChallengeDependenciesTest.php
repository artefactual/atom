<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeDependenciesTest extends TestCase
{
    /**
     * @dataProvider dependencyProvider
     */
    public function testManifestMatchesPinnedBundle(string $name)
    {
        $root = dirname(__DIR__, 3);
        $manifest = require $root.'/lib/challenge/dependencies.php';
        $entry = $manifest[$name];
        $this->assertSame('/js/challenge/'.$name.'-'.$entry['version'].'.js', $entry['asset']);
        $this->assertFileExists($root.$entry['asset']);
        $this->assertSame($entry['sha256'], hash_file('sha256', $root.$entry['asset']), 'Review the new upstream bundle and update its manifest checksum.');
        $this->assertFileExists($root.'/js/challenge/'.strtoupper($name).'-LICENSE.txt');
    }

    public function dependencyProvider(): array
    {
        return [['altcha'], ['botd']];
    }
}
