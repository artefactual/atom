<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

class QubitStaticPageNonce
{
    /**
     * Add the current CSP nonce to inline style and script tags in static page HTML.
     */
    public static function addNonceToInlineTags(string $content): string
    {
        $cspNonce = sfConfig::get('csp_nonce', '');
        if (empty($cspNonce)) {
            return $content;
        }

        $content = self::addNonceToInlineStyleTags($content, $cspNonce);

        return self::addNonceToInlineScriptTags($content, $cspNonce);
    }

    /**
     * Add the current CSP nonce to inline style tags that do not already define one.
     */
    protected static function addNonceToInlineStyleTags(string $content, string $cspNonce): string
    {
        return preg_replace_callback(
            '/<style\b([^>]*)>/i',
            function (array $matches) use ($cspNonce) {
                return self::addNonceToTag($matches[0], $matches[1], $cspNonce);
            },
            $content
        );
    }

    /**
     * Add the current CSP nonce to inline script tags while leaving external scripts unchanged.
     */
    protected static function addNonceToInlineScriptTags(string $content, string $cspNonce): string
    {
        return preg_replace_callback(
            '/<script\b([^>]*)>/i',
            function (array $matches) use ($cspNonce) {
                if (preg_match('/\bsrc\s*=/i', $matches[1])) {
                    return $matches[0];
                }

                return self::addNonceToTag($matches[0], $matches[1], $cspNonce);
            },
            $content
        );
    }

    /**
     * Inject a nonce attribute into an opening tag unless one is already present.
     */
    protected static function addNonceToTag(string $tag, string $attributes, string $cspNonce): string
    {
        if (preg_match('/\bnonce\s*=/i', $attributes)) {
            return $tag;
        }

        $trimmedAttributes = rtrim($attributes);
        if ('' !== $trimmedAttributes) {
            $trimmedAttributes = ' '.$trimmedAttributes;
        }

        return preg_replace('/>$/', $trimmedAttributes.' '.$cspNonce.'>', $tag);
    }
}
