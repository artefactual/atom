<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \ChallengeAction
 */
class ChallengeActionTest extends TestCase
{
    protected $action;

    protected function setUp(): void
    {
        // Mock AdminChallengeAction to isolate parseUrl()
        $this->action = $this->getMockBuilder(AdminChallengeAction::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['parseUrl'])
            ->getMock();
    }

    public function testParseUrl()
    {
        // Use reflection to access the protected parseUrl() method
        $reflection = new \ReflectionClass(AdminChallengeAction::class);
        $method = $reflection->getMethod('parseUrl');
        $method->setAccessible(true);

        // Test a full URL with path, query, and fragment
        $url = 'http://example.com/some/path?query=1#fragment';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('/some/path?query=1#fragment', $result);

        // Test a URL with only a path
        $url = 'http://example.com/some/path';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('/some/path', $result);

        // Test a URL with only a query
        $url = 'http://example.com/?query=1';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('/?query=1', $result);

        // Test a URL with only a fragment
        $url = 'http://example.com/#fragment';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('/#fragment', $result);

        // Test an empty URL
        $url = '';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('', $result);

        // Test a URL with no path, query, or fragment
        $url = 'http://example.com/';
        $result = $method->invoke($this->action, $url);
        $this->assertEquals('/', $result);
    }
}
