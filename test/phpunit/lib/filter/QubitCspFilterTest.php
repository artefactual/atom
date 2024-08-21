<?php

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitApcUniversalClassLoader
 * @covers \QubitCSP
 */
class QubitCspFilterTest extends TestCase
{
    public function setUp(): void
    {
        $app_yml = <<<'EOT'
all:
  csp:
    response_header: Content-Security-Policy-Report-Only
    directives: default-src 'self'; font-src 'self'; img-src 'self' blob:; script-src 'self' 'nonce'; style-src 'self' 'nonce'; worker-src 'self' blob:; frame-ancestors 'self';
EOT;

        $app_yml_multiline_greaterthan = <<<'EOT'
all:
  csp:
    response_header: Content-Security-Policy-Report-Only
    directives: >
      default-src 'self';
      font-src 'self';
      img-src 'self' blob:;
      script-src 'self' 'nonce';
      style-src 'self' 'nonce';
      worker-src 'self' blob:;
      frame-ancestors 'self';
EOT;

        $app_yml_multiline_pipe = <<<'EOT'
all:
  csp:
    response_header: Content-Security-Policy-Report-Only
    directives: |
      default-src 'self';
      font-src 'self';
      img-src 'self' blob:;
      script-src 'self' 'nonce';
      style-src 'self' 'nonce';
      worker-src 'self' blob:;
      frame-ancestors 'self';
EOT;

        $directory = [
            'app.yml' => $app_yml,
            'app_yml_multiline_greaterthan' => $app_yml_multiline_greaterthan,
            'app_yml_multiline_pipe' => $app_yml_multiline_pipe,
        ];

        $this->vfs = vfsStream::setup('root', null, $directory);
    }

    public function getCspResponseHeaderProvider()
    {
        return [
            'Standard app.yml with single line directive' => [
                'filename' => '/app.yml',
                'expected' => 'Content-Security-Policy-Report-Only',
            ],
        ];
    }

    /**
     * @dataProvider getCspResponseHeaderProvider
     *
     * @param mixed $filename
     * @param mixed $expected
     */
    public function testGetCspResponseHeader($filename, $expected)
    {
        // Read app.yml contents and populate sfConfig.
        $fn = $this->vfs->url().$filename;
        $handler = new sfDefineEnvironmentConfigHandler();
        $handler->initialize(['prefix' => 'app_']);
        $data = $handler->execute([$fn]);
        $data = preg_replace('/^<\?php\s*/', '', $data);
        eval($data);

        $qubitCspFilterInstance = new QubitCSP(sfContext::getInstance());
        $settingValue = $qubitCspFilterInstance->getCspResponseHeader(sfContext::getInstance());

        $this->assertSame($expected, $settingValue, 'Assert CSP response header read correctly.');
    }

    public function getCspDirectivesProvider()
    {
        return [
            'Standard app.yml with single line directive' => [
                'filename' => '/app.yml',
                'expected' => "default-src 'self'; font-src 'self'; img-src 'self' blob:; script-src 'self' 'nonce'; style-src 'self' 'nonce'; worker-src 'self' blob:; frame-ancestors 'self';",
            ],
            'app.yml with multiline directive - greaterthan yml string concatenator' => [
                'filename' => '/app_yml_multiline_greaterthan',
                'expected' => "default-src 'self'; font-src 'self'; img-src 'self' blob:; script-src 'self' 'nonce'; style-src 'self' 'nonce'; worker-src 'self' blob:; frame-ancestors 'self';",
            ],
            'app.yml with multiline directive - pipe yml string concatenator' => [
                'filename' => '/app_yml_multiline_pipe',
                'expected' => "default-src 'self'; font-src 'self'; img-src 'self' blob:; script-src 'self' 'nonce'; style-src 'self' 'nonce'; worker-src 'self' blob:; frame-ancestors 'self';",
            ],
        ];
    }

    /**
     * @dataProvider getCspDirectivesProvider
     *
     * @param mixed $filename
     * @param mixed $expected
     */
    public function testGetCspDirectives($filename, $expected)
    {
        // Read app.yml contents and populate sfConfig.
        $fn = $this->vfs->url().$filename;
        $handler = new sfDefineEnvironmentConfigHandler();
        $handler->initialize(['prefix' => 'app_']);
        $data = $handler->execute([$fn]);
        $data = preg_replace('/^<\?php\s*/', '', $data);
        eval($data);

        $qubitCspFilterInstance = new QubitCSP(sfContext::getInstance());
        $settingValue = $qubitCspFilterInstance->getCspDirectives(sfContext::getInstance());

        $this->assertSame($expected, trim($settingValue), 'CSP directive read from config did not match expected value.');
    }
}
