<?php

use PHPUnit\Framework\TestCase;

// Extend the class being tested to avoid calling
// the base constructor and attempting to start
// a gearman job
class arExportTestableJob extends arExportJob
{
    protected $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    public function runWithUserCulture($culture, callable $callback)
    {
        return $this->withUserCulture($culture, $callback);
    }
}

// Mock user class for setting and getting culture
class TestCultureUser
{
    private $culture;

    public function __construct($culture)
    {
        $this->culture = $culture;
    }

    public function getCulture()
    {
        return $this->culture;
    }

    public function setCulture($culture)
    {
        $this->culture = $culture;
    }
}

/**
 * @internal
 *
 * @covers \arExportJob
 */
class arExportJobTest extends TestCase
{
    public function testWithUserCultureWithRestoresCultureAfterRun()
    {
        $defaultCulture = 'fr';
        $testCulture = 'en';
        $testUser = new TestCultureUser($defaultCulture);
        $testJob = new arExportTestableJob($testUser);
        $testJob->runWithUserCulture($testCulture, function () {});
        $this->assertSame($defaultCulture, $testUser->getCulture());
    }

    public function testWithUserCultureWithChangesCultureDuringRun()
    {
        $defaultCulture = 'en';
        $testCulture = 'fr';
        $testUser = new TestCultureUser($defaultCulture);
        $testJob = new arExportTestableJob($testUser);
        $testJob->runWithUserCulture($testCulture, function () use ($testCulture, $testUser) {
            $this->assertSame($testCulture, $testUser->getCulture());
        });
        $this->assertSame($defaultCulture, $testUser->getCulture());
    }
}
