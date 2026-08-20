<?php

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \QubitStaticPageNonce
 */
class QubitStaticPageNonceTest extends TestCase
{
    protected function tearDown(): void
    {
        sfConfig::set('csp_nonce', null);
    }

    /**
     * Confirm inline tags are left unchanged when no nonce is available.
     */
    public function testAddNonceToInlineTagsLeavesInlineTagsUntouchedWhenNonceNotConfigured()
    {
        sfConfig::set('csp_nonce', '');

        $content = '<style>#top-bar { color: red; }</style><script>console.log("ok");</script>';

        $this->assertSame($content, QubitStaticPageNonce::addNonceToInlineTags($content));
    }

    /**
     * Confirm inline style tags receive the current nonce.
     */
    public function testAddNonceToInlineTagsAddsNonceToInlineStyleTag()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<style>#top-bar { color: red; }</style>';

        $this->assertSame(
            '<style nonce=abc123>#top-bar { color: red; }</style>',
            QubitStaticPageNonce::addNonceToInlineTags($content)
        );
    }

    /**
     * Confirm inline script tags receive the current nonce.
     */
    public function testAddNonceToInlineTagsAddsNonceToInlineScriptTag()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<script>console.log("ok");</script>';

        $this->assertSame(
            '<script nonce=abc123>console.log("ok");</script>',
            QubitStaticPageNonce::addNonceToInlineTags($content)
        );
    }

    /**
     * Confirm external script tags are ignored because CSP nonces are only needed for inline code.
     */
    public function testAddNonceToInlineTagsDoesNotAddNonceToExternalScriptTag()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<script src="/js/main.js"></script>';

        $this->assertSame($content, QubitStaticPageNonce::addNonceToInlineTags($content));
    }

    /**
     * Confirm an existing nonce attribute is preserved.
     */
    public function testAddNonceToInlineTagsDoesNotOverrideExistingNonceAttribute()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<style nonce="existing">#top-bar { color: red; }</style>';

        $this->assertSame($content, QubitStaticPageNonce::addNonceToInlineTags($content));
    }

    /**
     * Confirm unrelated HTML is left unchanged.
     */
    public function testAddNonceToInlineTagsLeavesContentWithoutInlineTagsUnchanged()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<div class="page">No inline content here.</div>';

        $this->assertSame($content, QubitStaticPageNonce::addNonceToInlineTags($content));
    }

    /**
     * Confirm the nonce is applied to multiple inline tags in the same fragment.
     */
    public function testAddNonceToInlineTagsAddsNonceToMultipleInlineTags()
    {
        sfConfig::set('csp_nonce', 'nonce=abc123');

        $content = '<style>#top-bar { color: red; }</style><script>console.log("ok");</script>';

        $this->assertSame(
            '<style nonce=abc123>#top-bar { color: red; }</style><script nonce=abc123>console.log("ok");</script>',
            QubitStaticPageNonce::addNonceToInlineTags($content)
        );
    }
}
