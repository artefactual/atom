<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitUserChallenge
 */
class QubitUserChallengeTest extends TestCase
{
    public function testCheckHeadlessReturnsTrueWhenTestIsDisabled()
    {
        // Create an instance of QubitUserChallenge with testHeadless set to false
        $userChallenge = new QubitUserChallenge(false, 'headless_cookie');

        // Call checkHeadless and assert it returns true
        $this->assertTrue($userChallenge->checkHeadless('test_key'));
    }

    public function testCheckHeadlessReturnsTrueWhenCookieMatches()
    {
        // Mock $_COOKIE
        $_COOKIE['headless_cookie'] = base64_encode('test_key:false');

        // Create an instance of QubitUserChallenge with testHeadless set to true
        $userChallenge = new QubitUserChallenge(true, 'headless_cookie');

        // Call checkHeadless and assert it returns true
        $this->assertTrue($userChallenge->checkHeadless('test_key'));
    }

    public function testCheckHeadlessReturnsFalseWhenCookieDoesNotMatch()
    {
        // Mock $_COOKIE
        $_COOKIE['headless_cookie'] = base64_encode('wrong_key:true');

        // Create an instance of QubitUserChallenge with testHeadless set to true
        $userChallenge = new QubitUserChallenge(true, 'headless_cookie');

        // Call checkHeadless and assert it returns false
        $this->assertFalse($userChallenge->checkHeadless('test_key'));
    }

    public function testCheckHeadlessReturnsFalseWhenCookieIsMissing()
    {
        // Clear $_COOKIE
        $_COOKIE = [];

        // Create an instance of QubitUserChallenge with testHeadless set to true
        $userChallenge = new QubitUserChallenge(true, 'headless_cookie');

        // Call checkHeadless and assert it returns false
        $this->assertFalse($userChallenge->checkHeadless('test_key'));
    }
}
