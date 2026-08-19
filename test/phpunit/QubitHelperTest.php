<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitHelper
 */
class QubitHelperTest extends TestCase
{
    /**
     * @dataProvider renderValueHtmlInjectionProvider
     *
     * @param mixed $input
     * @param mixed $expected
     */
    public function testRenderValueEscapesHtml($input, $expected)
    {
        $this->assertEquals($expected, render_value($input));
    }

    public function renderValueHtmlInjectionProvider()
    {
        return [
            'script tag' => [
                '<script>alert("xss")</script>',
                '<p>&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;</p>',
            ],
            'img onerror' => [
                '<img src=x onerror=alert(1)>',
                '<p>&lt;img src=x onerror=alert(1)&gt;</p>',
            ],
            'anchor with javascript href' => [
                '<a href="javascript:alert(1)">click</a>',
                '<p>&lt;a href=&quot;javascript:alert(1)&quot;&gt;click&lt;/a&gt;</p>',
            ],
            'event handler attribute' => [
                '<div onmouseover="alert(1)">hover</div>',
                '<p>&lt;div onmouseover=&quot;alert(1)&quot;&gt;hover&lt;/div&gt;</p>',
            ],
            'nested script in mark' => [
                '<mark><script>alert(1)</script></mark>',
                '<p>&lt;mark&gt;&lt;script&gt;alert(1)&lt;/script&gt;&lt;/mark&gt;</p>',
            ],
            'iframe' => [
                '<iframe src="https://example.com"></iframe>',
                '<p>&lt;iframe src=&quot;<a href="https://example.com">https://example.com</a>&quot;&gt;&lt;/iframe&gt;</p>',
            ],
            'plain text unchanged' => [
                'Hello world',
                '<p>Hello world</p>',
            ],
        ];
    }

    /**
     * @dataProvider renderValueWithHighlightsHtmlInjectionProvider
     *
     * @param mixed $input
     * @param mixed $expected
     */
    public function testRenderValueWithHighlightsEscapesHtml($input, $expected)
    {
        $this->assertEquals($expected, render_value_with_highlights($input));
    }

    public function renderValueWithHighlightsHtmlInjectionProvider()
    {
        return [
            'mark tags are preserved' => [
                '<mark>highlighted</mark>',
                '<mark>highlighted</mark>',
            ],
            'script tag is escaped' => [
                '<script>alert("xss")</script>',
                '&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;',
            ],
            'script inside mark is escaped' => [
                '<mark><script>alert(1)</script></mark>',
                '<mark>&lt;script&gt;alert(1)&lt;/script&gt;</mark>',
            ],
            'img onerror is escaped' => [
                '<img src=x onerror=alert(1)>',
                '&lt;img src=x onerror=alert(1)&gt;',
            ],
            'anchor with javascript href is escaped' => [
                '<a href="javascript:alert(1)">click</a>',
                '&lt;a href=&quot;javascript:alert(1)&quot;&gt;click&lt;/a&gt;',
            ],
            'event handler attribute is escaped' => [
                '<div onmouseover="alert(1)">hover</div>',
                '&lt;div onmouseover=&quot;alert(1)&quot;&gt;hover&lt;/div&gt;',
            ],
            'iframe is escaped' => [
                '<iframe src="https://example.com"></iframe>',
                '&lt;iframe src=&quot;<a href="https://example.com">https://example.com</a>&quot;&gt;&lt;/iframe&gt;',
            ],
            'fake mark with attributes is escaped' => [
                '<mark onmouseover="alert(1)">text</mark>',
                '&lt;mark onmouseover=&quot;alert(1)&quot;&gt;text&lt;/mark&gt;',
            ],
            'mark with extra spaces is escaped' => [
                '<mark >text</mark >',
                '&lt;mark &gt;text&lt;/mark &gt;',
            ],
            'orphaned closing mark is escaped' => [
                'text</mark>',
                'text&lt;/mark&gt;',
            ],
            'orphaned opening mark is escaped' => [
                '<mark>text',
                '&lt;mark&gt;text',
            ],
            'unbalanced marks' => [
                '<mark>first</mark><mark>second',
                '&lt;mark&gt;first&lt;/mark&gt;&lt;mark&gt;second',
            ],
            'markdown link with marks in text and url' => [
                '[<mark>slug</mark>](/index.php/<mark>slug</mark>)',
                '<a href="/index.php/slug"><mark>slug</mark></a>',
            ],
            'markdown link with external url' => [
                '[<mark>slug</mark>](https://example.com/<mark>slug</mark>)',
                '<a href="https://example.com/slug"><mark>slug</mark></a>',
            ],
            'markdown link with relative url' => [
                '[<mark>slug</mark>](/<mark>slug</mark>)',
                '<a href="/slug"><mark>slug</mark></a>',
            ],
            'mixed marks and other html' => [
                '<mark>safe</mark><script>alert(1)</script><mark>also safe</mark>',
                '<mark>safe</mark>&lt;script&gt;alert(1)&lt;/script&gt;<mark>also safe</mark>',
            ],
            'plain text unchanged' => [
                'Hello world',
                'Hello world',
            ],
            'multiple marks preserved' => [
                'Some <mark>first</mark> and <mark>second</mark> highlights',
                'Some <mark>first</mark> and <mark>second</mark> highlights',
            ],
        ];
    }
}
