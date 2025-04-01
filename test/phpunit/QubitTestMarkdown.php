<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitApcUniversalClassLoader::findFile
 * @covers \QubitMarkdown::__construct
 * @covers \QubitMarkdown::getInstance
 * @covers \QubitMarkdown::getUnescapedString
 * @covers \QubitMarkdown::parse
 */
class QubitTestMarkdown extends TestCase
{
    protected $markdown;

    public function testParseSafeModeFalse()
    {
        $testCases = [
            // Basic Quotes
            '<a href="/test" title="&quot;Test&quot;">&quot;Test&quot;</a>' => '<p><a href="/test" title="&quot;Test&quot;">&quot;Test&quot;</a></p>',
            '""""' => '<p>&quot;&quot;&quot;&quot;</p>',
            'Some text "with double quotes" here' => '<p>Some text &quot;with double quotes&quot; here</p>',
            'No quotes' => '<p>No quotes</p>',

            // Basic formatting
            '*italic*' => '<p><em>italic</em></p>',
            '**bold**' => '<p><strong>bold</strong></p>',
            '***bold italic***' => '<p><strong><em>bold italic</em></strong></p>',

            // Headers
            '# Heading 1' => '<h1>Heading 1</h1>',
            '## Heading 2' => '<h2>Heading 2</h2>',
            '### Heading 3' => '<h3>Heading 3</h3>',

            // Links
            '[Link](https://example.com)' => '<p><a href="https://example.com">Link</a></p>',

            // Images
            '![Alt text](https://example.com/image.jpg)' => '<p><img src="https://example.com/image.jpg" alt="Alt text" /></p>',

            // Code Blocks
            '`inline code`' => '<p><code>inline code</code></p>',
            "```\nblock code\n```" => '<pre><code>block code</code></pre>',

            // Escaped Characters
            '\\*Not italic\\*' => '<p>*Not italic*</p>',
            '\\# Not a header' => '<p># Not a header</p>',

            // Blockquote
            '> This is a quote' => '<blockquote>
<p>This is a quote</p>
</blockquote>',

            // Lists
            '* Item 1
* Item 2
* Item 3' => '<ul>
<li>Item 1</li>
<li>Item 2</li>
<li>Item 3</li>
</ul>',
            '1. Item 1
2. Item 2
3. Item 3' => '<ol>
<li>Item 1</li>
<li>Item 2</li>
<li>Item 3</li>
</ol>',

            // Horizontal Rules
            '***' => '<hr />',
            '___' => '<hr />',
        ];

        foreach ($testCases as $input => $expected) {
            $output = QubitMarkdown::getInstance()->parse($input, ['safeMode' => false]);

            $this->assertEquals($expected, $output);
        }
    }

    public function testParseSafeModeTrue()
    {
        $testCases = [
            // Basic formatting
            '*italic*' => '<p><em>italic</em></p>',
            '**bold**' => '<p><strong>bold</strong></p>',
            '***bold italic***' => '<p><strong><em>bold italic</em></strong></p>',

            // Headers
            '# Heading 1' => '<h1>Heading 1</h1>',
            '## Heading 2' => '<h2>Heading 2</h2>',
            '### Heading 3' => '<h3>Heading 3</h3>',

            // Links
            '[Link](https://example.com)' => '<p><a href="https://example.com">Link</a></p>',

            // Images
            '![Alt text](https://example.com/image.jpg)' => '<p><img src="https://example.com/image.jpg" alt="Alt text" /></p>',

            // Code Blocks
            '`inline code`' => '<p><code>inline code</code></p>',
            "```\nblock code\n```" => '<pre><code>block code</code></pre>',

            // Escaped Characters
            '\\*Not italic\\*' => '<p>*Not italic*</p>',
            '\\# Not a header' => '<p># Not a header</p>',

            // Blockquote
            '> This is a quote' => '<blockquote>
<p>This is a quote</p>
</blockquote>',

            // Lists
            '* Item 1
* Item 2
* Item 3' => '<ul>
<li>Item 1</li>
<li>Item 2</li>
<li>Item 3</li>
</ul>',
            '1. Item 1
2. Item 2
3. Item 3' => '<ol>
<li>Item 1</li>
<li>Item 2</li>
<li>Item 3</li>
</ol>',

            // Horizontal Rules
            '***' => '<hr />',
            '___' => '<hr />',
        ];

        foreach ($testCases as $input => $expected) {
            $output = QubitMarkdown::getInstance()->parse($input, ['safeMode' => true]);

            $this->assertEquals($expected, $output);
        }
    }

    public function testParseHtmlEntitiesSafeModeFalse()
    {
        $testCases = [
            // JavaScript script
            '<script>alert("XSS")</script>' => '<script>alert("XSS")</script>',

            // Inline JavaScript
            '<script>console.log("test")</script>' => '<script>console.log("test")</script>',

            // PHP Code
            '<?php echo "Hello, World!"; ?>' => '<p>&lt;?php echo &quot;Hello, World!&quot;; ?&gt;</p>',

            // HTML with PHP Code
            '<div><?php echo "Test"; ?></div>' => '<div><?php echo "Test"; ?></div>',
        ];

        foreach ($testCases as $input => $expected) {
            $output = QubitMarkdown::getInstance()->parse($input, ['safeMode' => false]);
            $this->assertEquals($expected, $output);
        }
    }

    public function testParseHtmlEntitiesSafeModeTrue()
    {
        $testCases = [
            // JavaScript
            '<script>alert("XSS")</script>' => '<p>&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;</p>',

            // Inline JavaScript
            '<script>console.log("test")</script>' => '<p>&lt;script&gt;console.log(&quot;test&quot;)&lt;/script&gt;</p>',

            // PHP Code
            '<?php echo "Hello, World!"; ?>' => '<p>&lt;?php echo &quot;Hello, World!&quot;; ?&gt;</p>',

            // HTML with PHP Code
            '<div><?php echo "Test"; ?></div>' => '<p>&lt;div&gt;&lt;?php echo &quot;Test&quot;; ?&gt;&lt;/div&gt;</p>',
        ];

        foreach ($testCases as $input => $expected) {
            $output = QubitMarkdown::getInstance()->parse($input, ['safeMode' => true]);
            $this->assertEquals($expected, $output);
        }
    }
}
