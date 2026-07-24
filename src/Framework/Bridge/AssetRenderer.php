<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

final readonly class AssetRenderer
{
    private const B5_JAVASCRIPT_REPLACEMENTS = [
        '/vendor/modernizr' => '/js/modernizrInputShim.js',
    ];

    private const B5_BUNDLED_JAVASCRIPTS = [
        '/vendor/jquery',
        '/plugins/sfDrupalPlugin/vendor/drupal/misc/drupal',
        '/plugins/sfDrupalPlugin/vendor/drupal/misc/tableheader',
        '/plugins/sfDrupalPlugin/vendor/drupal/modules/user/user',
        '/vendor/yui/yahoo-dom-event/yahoo-dom-event',
        '/vendor/imageflow/imageflow.packed.js',
        'qubit',
        'treeView',
        'clipboard',
    ];

    public function stylesheets(ResponseAdapter $response): string
    {
        $html = '';

        foreach ($response->getStylesheets() as $source => $options) {
            $options += [
                'media' => 'screen',
                'rel' => 'stylesheet',
            ];
            $options['href'] = $this->path(
                (string) $source,
                'css',
                'css',
            );
            $html .= $this->tag('link', $options)."\n";
        }

        return $html;
    }

    public function javaScripts(ResponseAdapter $response): string
    {
        $html = '';

        foreach ($response->getJavascripts() as $source => $options) {
            if (Configuration::get('app_b5_theme', false)) {
                if (isset(self::B5_JAVASCRIPT_REPLACEMENTS[$source])) {
                    $source = self::B5_JAVASCRIPT_REPLACEMENTS[$source];
                } elseif (in_array(
                    (string) $source,
                    self::B5_BUNDLED_JAVASCRIPTS,
                    true,
                )) {
                    continue;
                }
            }

            $options += ['defer' => true];
            $options['src'] = $this->path(
                (string) $source,
                'js',
                'js',
            );
            $html .= $this->contentTag('script', '', $options)."\n";
        }

        return $html;
    }

    public function metas(ResponseAdapter $response): string
    {
        $html = '';

        foreach ($response->getMetas() as $name => $content) {
            $html .= $this->tag('meta', [
                'name' => $name,
                'content' => $content,
            ])."\n";
        }

        return $html;
    }

    public function httpMetas(ResponseAdapter $response): string
    {
        $html = '';

        foreach ($response->getHttpMetas() as $name => $content) {
            $html .= $this->tag('meta', [
                'http-equiv' => $name,
                'content' => $content,
            ])."\n";
        }

        return $html;
    }

    public function inject(
        string $html,
        ResponseAdapter $response,
        bool $stylesheetsIncluded,
        bool $javaScriptsIncluded,
        bool $metasIncluded,
        bool $httpMetasIncluded,
    ): string {
        $head = '';

        if (!$httpMetasIncluded) {
            $head .= $this->httpMetas($response);
        }

        if (!$metasIncluded) {
            $head .= $this->metas($response);
        }

        if (!$stylesheetsIncluded) {
            $head .= $this->stylesheets($response);
        }

        if ('' !== $head && str_contains($html, '</head>')) {
            $html = str_replace('</head>', $head.'</head>', $html);
        }

        if (!$javaScriptsIncluded) {
            $scripts = $this->javaScripts($response);

            if ('' !== $scripts && str_contains($html, '</body>')) {
                $html = str_replace(
                    '</body>',
                    $scripts.'</body>',
                    $html,
                );
            }
        }

        return $html;
    }

    private function path(
        string $source,
        string $directory,
        string $extension,
    ): string {
        if (
            preg_match('#^(?:https?:)?//#i', $source)
            || str_starts_with($source, '/')
        ) {
            $path = $source;
        } else {
            $path = '/'.$directory.'/'.$source;
        }

        $query = '';

        if (false !== $position = strpos($path, '?')) {
            $query = substr($path, $position);
            $path = substr($path, 0, $position);
        }

        if (!str_contains(basename($path), '.')) {
            $path .= '.'.$extension;
        }

        return $path.$query;
    }

    private function tag(string $name, array $attributes): string
    {
        return '<'.$name.$this->attributes($attributes).' />';
    }

    private function contentTag(
        string $name,
        string $content,
        array $attributes,
    ): string {
        return '<'.$name.$this->attributes($attributes).'>'
            .$content.'</'.$name.'>';
    }

    private function attributes(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $name => $value) {
            if (null === $value || false === $value) {
                continue;
            }

            $html .= sprintf(
                ' %s="%s"',
                $name,
                htmlspecialchars(
                    true === $value ? (string) $name : (string) $value,
                    \ENT_QUOTES | \ENT_SUBSTITUTE,
                    'UTF-8',
                ),
            );
        }

        return $html;
    }
}
