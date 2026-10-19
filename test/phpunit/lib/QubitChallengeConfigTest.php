<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

require_once __DIR__.'/../../../lib/QubitChallengeConfig.class.php';

/**
 * @internal
 *
 * @coversNothing
 */
class QubitChallengeConfigTest extends TestCase
{
    protected string $directory;
    protected string $configFile;
    protected string $cacheDir;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/atom-challenge-config-'.bin2hex(random_bytes(8));
        mkdir($this->directory, 0700);
        $this->configFile = $this->directory.'/appChallenge.yml';
        $this->cacheDir = $this->directory.'/cache';
        file_put_contents($this->configFile, "activated: true\nsalt: test-only\n");
    }

    protected function tearDown(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    public function testCachesParsedConfigurationWithoutRewritingOnHits()
    {
        $expected = ['activated' => true, 'salt' => 'test-only'];
        $this->assertSame($expected, QubitChallengeConfig::load($this->configFile, $this->cacheDir));
        $cacheFile = glob($this->cacheDir.'/*.php')[0];
        $this->assertSame(0600, fileperms($cacheFile) & 0777);
        $inode = fileinode($cacheFile);

        $this->assertSame($expected, QubitChallengeConfig::load($this->configFile, $this->cacheDir));
        clearstatcache();
        $this->assertSame($inode, fileinode($cacheFile));
        $this->assertCount(1, glob($this->cacheDir.'/*'));
    }

    public function testDetectsChangesWithTheSameSizeAndTimestamp()
    {
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        $mtime = filemtime($this->configFile);
        $size = filesize($this->configFile);
        file_put_contents($this->configFile, "activated: true\nsalt: new-value\n");
        touch($this->configFile, $mtime);
        clearstatcache();
        $this->assertSame($size, filesize($this->configFile));
        $this->assertSame($mtime, filemtime($this->configFile));

        $this->assertSame(
            ['activated' => true, 'salt' => 'new-value'],
            QubitChallengeConfig::load($this->configFile, $this->cacheDir)
        );
    }

    public function testDeletedSourceNeverUsesStaleConfiguration()
    {
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        unlink($this->configFile);

        $this->assertNull(QubitChallengeConfig::load($this->configFile, $this->cacheDir));
    }

    public function testUnreadableSourceDisablesTheChallenge()
    {
        $root = vfsStream::setup('challenge');
        vfsStream::newFile('appChallenge.yml', 0000)->at($root)->setContent('activated: true');

        $this->assertNull(QubitChallengeConfig::load($root->url().'/appChallenge.yml', $this->cacheDir));
    }

    public function testCannotCreateCacheDirectoryFallsBackToYaml()
    {
        file_put_contents($this->cacheDir, 'not a directory');

        $this->assertTrue(QubitChallengeConfig::load($this->configFile, $this->cacheDir)['activated']);
        $this->assertSame('not a directory', file_get_contents($this->cacheDir));
    }

    public function testUnwritableCacheDirectoryFallsBackToYaml()
    {
        $root = vfsStream::setup('challenge');
        vfsStream::newDirectory('cache', 0555)->at($root);

        $this->assertTrue(QubitChallengeConfig::load($this->configFile, $root->url().'/cache')['activated']);
        $this->assertSame(['.', '..'], scandir($root->url().'/cache'));
    }

    public function testInvalidPhpCacheIsRebuilt()
    {
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        $cacheFile = glob($this->cacheDir.'/*.php')[0];
        file_put_contents($cacheFile, '<?php return [broken;');

        $this->assertTrue(QubitChallengeConfig::load($this->configFile, $this->cacheDir)['activated']);
        $cached = include $cacheFile;
        $this->assertTrue($cached['config']['activated']);
    }

    public function testInvalidYamlNeverUsesStaleConfiguration()
    {
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        file_put_contents($this->configFile, "activated: [\n");

        $this->expectException(InvalidArgumentException::class);
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
    }

    public function testEmptyYamlDisablesTheChallenge()
    {
        file_put_contents($this->configFile, '');

        $this->assertSame([], QubitChallengeConfig::load($this->configFile, $this->cacheDir));
    }

    public function testNonArrayYamlIsRejected()
    {
        file_put_contents($this->configFile, 'false');

        $this->expectException(InvalidArgumentException::class);
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
    }

    public function testCacheFilesAreIsolatedBySourcePath()
    {
        $otherConfig = $this->directory.'/other.yml';
        copy($this->configFile, $otherConfig);
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        QubitChallengeConfig::load($otherConfig, $this->cacheDir);

        $this->assertCount(2, glob($this->cacheDir.'/*.php'));
    }

    public function testSourcePathAliasesShareTheSameCache()
    {
        mkdir($this->directory.'/subdirectory');
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        QubitChallengeConfig::load($this->directory.'/subdirectory/../appChallenge.yml', $this->cacheDir);

        $this->assertCount(1, glob($this->cacheDir.'/*.php'));
    }

    public function testCacheHitDoesNotLoadSymfonyParser()
    {
        QubitChallengeConfig::load($this->configFile, $this->cacheDir);
        $code = $this->loaderCode()
            .'$config = QubitChallengeConfig::load('.var_export($this->configFile, true).', '.var_export($this->cacheDir, true).');'
            .'echo json_encode([$config["activated"], class_exists("sfYaml", false)]);';

        $this->assertSame('[true,false]', $this->runPhp($code));
    }

    public function testUpdatesWithOpcacheTimestampValidationDisabled()
    {
        $code = $this->loaderCode()
            .'$source = '.var_export($this->configFile, true).'; $dir = '.var_export($this->cacheDir, true).';'
            .'QubitChallengeConfig::load($source, $dir); $cache = glob($dir."/*.php")[0];'
            .'if (!function_exists("opcache_compile_file") || !opcache_compile_file($cache)) { echo "unavailable"; exit; }'
            .'file_put_contents($source, "activated: true\nsalt: new-value\n");'
            .'$config = QubitChallengeConfig::load($source, $dir);'
            .'$cached = include $cache;'
            .'echo json_encode([$config["salt"], $cached["config"]["salt"]]);';
        $result = $this->runPhp($code, ['-d', 'opcache.enable_cli=1', '-d', 'opcache.validate_timestamps=0', '-d', 'opcache.file_update_protection=0']);
        if ('unavailable' === $result) {
            $this->markTestSkipped('OPcache is not available in CLI.');
        }

        $this->assertSame('["new-value","new-value"]', $result);
    }

    public function testConcurrentCacheCreation()
    {
        $barrier = $this->directory.'/start';
        $code = $this->loaderCode()
            .'while (!file_exists('.var_export($barrier, true).')) { usleep(1000); }'
            .'for ($i = 0; $i < 20; ++$i) {'
            .'$config = QubitChallengeConfig::load('.var_export($this->configFile, true).', '.var_export($this->cacheDir, true).');'
            .'if (($config["salt"] ?? null) !== "test-only") { exit(1); } }';
        $workers = [];
        for ($i = 0; $i < 8; ++$i) {
            $process = proc_open([PHP_BINARY, '-d', 'display_errors=stderr', '-r', $code], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            $this->assertIsResource($process);
            $workers[] = [$process, $pipes];
        }
        touch($barrier);
        foreach ($workers as [$process, $pipes]) {
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $this->assertSame(0, proc_close($process), $errors);
            $this->assertSame('', $output.$errors);
        }
        $this->assertCount(1, glob($this->cacheDir.'/*'));
        $cached = include glob($this->cacheDir.'/*.php')[0];
        $this->assertSame('test-only', $cached['config']['salt']);
    }

    protected function loaderCode(): string
    {
        return 'require '.var_export(realpath(__DIR__.'/../../../lib/QubitChallengeConfig.class.php'), true).';';
    }

    protected function runPhp(string $code, array $options = []): string
    {
        $process = proc_open(array_merge([PHP_BINARY], $options, ['-d', 'display_errors=stderr', '-r', $code]), [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ], $pipes);
        $this->assertIsResource($process);
        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $errors);
        $this->assertSame('', $errors);

        return $output;
    }
}
