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

    public function testAltchaStylesheetMatchesCspHash()
    {
        $root = dirname(__DIR__, 3);
        $entry = (require $root.'/lib/challenge/dependencies.php')['altcha'];
        $bundle = file_get_contents($root.$entry['asset']);
        // The pinned v3 build injects one static single-quoted JS string.
        // Decode JS escapes without evaluating any downloaded code.
        $this->assertSame(1, preg_match('/^const css = (.+);$/m', $bundle, $matches));
        $this->assertSame("'", $matches[1][0]);
        $css = preg_replace_callback('/\\\\(n|r|t|\\\\|\')/', static function ($match) {
            return ['n' => "\n", 'r' => "\r", 't' => "\t", '\\' => '\\', "'" => "'"][$match[1]];
        }, substr($matches[1], 1, -1));
        $this->assertSame($entry['style_hash'], 'sha256-'.base64_encode(hash('sha256', $css, true)));
    }
}
