<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

use Atom\Framework\Bridge\Context;

defined('ESC_ENTITIES') || define('ESC_ENTITIES', 'esc_entities');
defined('ESC_SPECIALCHARS')
    || define('ESC_SPECIALCHARS', 'esc_specialchars');
defined('ESC_RAW') || define('ESC_RAW', 'esc_raw');

if (!function_exists('__')) {
    function __(mixed $message, array $arguments = [], ?string $catalogue = null): string
    {
        if (!Context::hasInstance()) {
            return strtr((string) $message, $arguments);
        }

        return Context::getInstance()->i18n->__(
            (string) $message,
            $arguments,
            $catalogue,
        );
    }
}

if (!function_exists('use_helper')) {
    function use_helper(mixed ...$helpers): void
    {
        Context::getInstance()->getConfiguration()->loadHelpers($helpers);
    }
}

if (!function_exists('get_partial')) {
    function get_partial(string $name, array $variables = []): string
    {
        return Context::getInstance()
            ->getViewRuntime()
            ->getPartial($name, $variables);
    }
}

if (!function_exists('include_partial')) {
    function include_partial(string $name, array $variables = []): void
    {
        echo get_partial($name, $variables);
    }
}

if (!function_exists('get_component')) {
    function get_component(
        string $module,
        string $component,
        array $variables = [],
    ): string {
        return Context::getInstance()
            ->getViewRuntime()
            ->getComponent($module, $component, $variables);
    }
}

if (!function_exists('include_component')) {
    function include_component(
        string $module,
        string $component,
        array $variables = [],
    ): void {
        echo get_component($module, $component, $variables);
    }
}

if (!function_exists('get_component_slot')) {
    function get_component_slot(
        string $name,
        array $variables = [],
    ): string {
        return Context::getInstance()
            ->getViewRuntime()
            ->getComponentSlot($name, $variables);
    }
}

if (!function_exists('include_component_slot')) {
    function include_component_slot(
        string $name,
        array $variables = [],
    ): void {
        echo get_component_slot($name, $variables);
    }
}

if (!function_exists('has_component_slot')) {
    function has_component_slot(string $name): bool
    {
        return Context::getInstance()
            ->getViewRuntime()
            ->hasComponentSlot($name);
    }
}

if (!function_exists('decorate_with')) {
    function decorate_with(false|string $layout): void
    {
        Context::getInstance()
            ->getViewRuntime()
            ->decorateWith($layout);
    }
}

if (!function_exists('slot')) {
    function slot(string $name, mixed $value = null): void
    {
        Context::getInstance()
            ->getViewRuntime()
            ->startSlot($name, $value);
    }
}

if (!function_exists('end_slot')) {
    function end_slot(): void
    {
        Context::getInstance()->getViewRuntime()->endSlot();
    }
}

if (!function_exists('has_slot')) {
    function has_slot(string $name): bool
    {
        return Context::getInstance()->getViewRuntime()->hasSlot($name);
    }
}

if (!function_exists('get_slot')) {
    function get_slot(string $name, string $default = ''): string
    {
        return Context::getInstance()
            ->getViewRuntime()
            ->getSlot($name, $default);
    }
}

if (!function_exists('include_slot')) {
    function include_slot(string $name, string $default = ''): bool
    {
        $content = get_slot($name, $default);

        if ('' === $content) {
            return false;
        }

        echo $content;

        return true;
    }
}

if (!function_exists('esc_specialchars')) {
    function esc_specialchars(mixed $value): mixed
    {
        return is_string($value)
            ? htmlspecialchars(
                $value,
                \ENT_QUOTES | \ENT_SUBSTITUTE,
                (string) sfConfig::get('sf_charset', 'UTF-8'),
            )
            : $value;
    }
}

if (!function_exists('esc_entities')) {
    function esc_entities(mixed $value): mixed
    {
        return is_string($value)
            ? htmlentities(
                $value,
                \ENT_QUOTES | \ENT_SUBSTITUTE,
                (string) sfConfig::get('sf_charset', 'UTF-8'),
            )
            : $value;
    }
}

if (!function_exists('esc_raw')) {
    function esc_raw(mixed $value): mixed
    {
        return $value;
    }
}

if (!function_exists('_parse_attributes')) {
    function _parse_attributes(mixed $attributes): array
    {
        if (is_array($attributes)) {
            return $attributes;
        }

        if (!is_string($attributes) || '' === trim($attributes)) {
            return [];
        }

        parse_str(str_replace(' ', '&', trim($attributes)), $parsed);

        return $parsed;
    }
}

if (!function_exists('_tag_options')) {
    function _tag_options(mixed $attributes = []): string
    {
        $html = '';

        foreach (_parse_attributes($attributes) as $name => $value) {
            if (false === $value || null === $value) {
                continue;
            }

            if (true === $value) {
                $value = $name;
            } elseif (is_array($value)) {
                $value = implode(' ', $value);
            }

            $html .= sprintf(
                ' %s="%s"',
                $name,
                htmlspecialchars(
                    (string) $value,
                    \ENT_QUOTES | \ENT_SUBSTITUTE,
                    'UTF-8',
                ),
            );
        }

        return $html;
    }
}

if (!function_exists('tag')) {
    function tag(
        string $name,
        mixed $attributes = [],
        bool $open = false,
    ): string {
        if ('' === $name) {
            return '';
        }

        return '<'.$name._tag_options($attributes).($open ? '>' : ' />');
    }
}

if (!function_exists('content_tag')) {
    function content_tag(
        string $name,
        mixed $content = '',
        mixed $attributes = [],
    ): string {
        if ('' === $name) {
            return '';
        }

        return '<'.$name._tag_options($attributes).'>'
            .$content.'</'.$name.'>';
    }
}

if (!function_exists('url_for')) {
    function url_for(
        array|string $target,
        array|bool $parameters = [],
        bool $absolute = false,
    ): string {
        if (is_bool($parameters)) {
            $absolute = $parameters;
            $parameters = [];
        }

        if (
            is_string($target)
            && '' !== $target
            && '@' !== $target[0]
            && !str_contains($target, '/')
            && [] !== $parameters
        ) {
            return Context::getInstance()
                ->getRouting()
                ->generate($target, $parameters, $absolute);
        }

        $url = Context::getInstance()->urlFor($target);

        if (!$absolute || preg_match('#^https?://#i', $url)) {
            return $url;
        }

        return Context::getInstance()
            ->getRequest()
            ->getUriPrefix().$url;
    }
}

if (!function_exists('link_to')) {
    function link_to(
        mixed $name,
        array|string $target,
        array $attributes = [],
    ): string {
        $absolute = (bool) ($attributes['absolute'] ?? false);
        unset($attributes['absolute']);
        $attributes['href'] = url_for($target, $absolute);

        return content_tag(
            'a',
            '' === (string) $name ? $attributes['href'] : (string) $name,
            $attributes,
        );
    }
}

if (!function_exists('public_path')) {
    function public_path(string $path, bool $absolute = false): string
    {
        $request = Context::getInstance()->getRequest();
        $path = '/'.ltrim($path, '/');
        $root = rtrim($request->getRelativeUrlRoot(), '/');
        $path = $root.$path;

        return $absolute ? $request->getUriPrefix().$path : $path;
    }
}

if (!function_exists('image_path')) {
    function image_path(string $source, bool $absolute = false): string
    {
        if (
            preg_match('#^(?:https?:)?//#i', $source)
            || str_starts_with($source, '/')
        ) {
            $path = $source;
        } else {
            $path = '/images/'.$source;

            if (!str_contains(basename($path), '.')) {
                $path .= '.png';
            }
        }

        return $absolute
            ? Context::getInstance()->getRequest()->getUriPrefix().$path
            : $path;
    }
}

if (!function_exists('image_tag')) {
    function image_tag(string $source, array $attributes = []): string
    {
        if ('' === $source) {
            return '';
        }

        if (isset($attributes['size'])) {
            [$attributes['width'], $attributes['height']] = array_pad(
                explode('x', (string) $attributes['size'], 2),
                2,
                null,
            );
            unset($attributes['size']);
        }

        $absolute = (bool) ($attributes['absolute'] ?? false);
        unset($attributes['absolute']);
        $attributes['src'] = image_path($source, $absolute);

        return tag('img', $attributes);
    }
}

if (!function_exists('javascript_path')) {
    function javascript_path(
        string $source,
        bool $absolute = false,
    ): string {
        if (
            preg_match('#^(?:https?:)?//#i', $source)
            || str_starts_with($source, '/')
        ) {
            $path = $source;
        } else {
            $path = '/js/'.$source;
        }

        if (!str_contains(basename($path), '.')) {
            $path .= '.js';
        }

        return $absolute
            ? Context::getInstance()->getRequest()->getUriPrefix().$path
            : $path;
    }
}

if (!function_exists('javascript_include_tag')) {
    function javascript_include_tag(
        string $source,
        array $attributes = [],
    ): string {
        $absolute = (bool) ($attributes['absolute'] ?? false);
        unset($attributes['absolute']);
        $attributes['src'] = javascript_path($source, $absolute);

        return content_tag('script', '', $attributes);
    }
}

if (!function_exists('stylesheet_path')) {
    function stylesheet_path(
        string $source,
        bool $absolute = false,
    ): string {
        if (
            preg_match('#^(?:https?:)?//#i', $source)
            || str_starts_with($source, '/')
        ) {
            $path = $source;
        } else {
            $path = '/css/'.$source;
        }

        if (!str_contains(basename($path), '.')) {
            $path .= '.css';
        }

        return $absolute
            ? Context::getInstance()->getRequest()->getUriPrefix().$path
            : $path;
    }
}

if (!function_exists('stylesheet_tag')) {
    function stylesheet_tag(
        string $source,
        array $attributes = [],
    ): string {
        $absolute = (bool) ($attributes['absolute'] ?? false);
        unset($attributes['absolute']);
        $attributes += ['media' => 'screen', 'rel' => 'stylesheet'];
        $attributes['href'] = stylesheet_path($source, $absolute);

        return tag('link', $attributes);
    }
}

if (!function_exists('include_title')) {
    function include_title(): void
    {
        echo content_tag(
            'title',
            esc_specialchars(
                Context::getInstance()->getResponse()->getTitle(),
            ),
        )."\n";
    }
}

if (!function_exists('get_stylesheets')) {
    function get_stylesheets(): string
    {
        return Context::getInstance()
            ->getViewRuntime()
            ->getStylesheets();
    }
}

if (!function_exists('include_stylesheets')) {
    function include_stylesheets(): void
    {
        echo get_stylesheets();
    }
}

if (!function_exists('get_javascripts')) {
    function get_javascripts(): string
    {
        return Context::getInstance()
            ->getViewRuntime()
            ->getJavaScripts();
    }
}

if (!function_exists('include_javascripts')) {
    function include_javascripts(): void
    {
        echo get_javascripts();
    }
}

if (!function_exists('get_metas')) {
    function get_metas(): string
    {
        return Context::getInstance()->getViewRuntime()->getMetas();
    }
}

if (!function_exists('include_metas')) {
    function include_metas(): void
    {
        echo get_metas();
    }
}

if (!function_exists('include_http_metas')) {
    function include_http_metas(): void
    {
        echo Context::getInstance()
            ->getViewRuntime()
            ->getHttpMetas();
    }
}

if (!function_exists('use_stylesheet')) {
    function use_stylesheet(
        string $source,
        string $position = '',
        array $options = [],
    ): void {
        Context::getInstance()
            ->getResponse()
            ->addStylesheet($source, $position, $options);
    }
}

if (!function_exists('use_javascript')) {
    function use_javascript(
        string $source,
        string $position = '',
        array $options = [],
    ): void {
        Context::getInstance()
            ->getResponse()
            ->addJavaScript($source, $position, $options);
    }
}

if (!function_exists('format_date')) {
    function format_date(
        mixed $date,
        array|string|null $format = 'd',
        ?string $culture = null,
        ?string $charset = null,
    ): ?string {
        $culture ??= Context::getInstance()->getUser()->getCulture();

        return (new sfDateFormat($culture))->format(
            $date,
            $format,
            null,
            $charset ?? (string) sfConfig::get('sf_charset', 'UTF-8'),
        );
    }
}

if (!function_exists('format_datetime')) {
    function format_datetime(
        mixed $date,
        array|string|null $format = 'F',
        ?string $culture = null,
        ?string $charset = null,
    ): ?string {
        return format_date($date, $format, $culture, $charset);
    }
}

if (!function_exists('format_language')) {
    function format_language(
        string $language,
        ?string $culture = null,
    ): string {
        $culture ??= Context::getInstance()->getUser()->getCulture();

        return sfCultureInfo::getInstance($culture)
            ->getLanguage($language);
    }
}

if (!function_exists('format_country')) {
    function format_country(
        string $country,
        ?string $culture = null,
    ): string {
        $culture ??= Context::getInstance()->getUser()->getCulture();

        return sfCultureInfo::getInstance($culture)->getCountry($country);
    }
}

if (!function_exists('format_number')) {
    function format_number(
        float|int|null $number,
        ?string $culture = null,
    ): ?string {
        if (null === $number) {
            return null;
        }

        $culture ??= Context::getInstance()->getUser()->getCulture();

        return (new sfNumberFormat($culture))->format($number);
    }
}

if (!function_exists('format_currency')) {
    function format_currency(
        float|int|null $amount,
        ?string $currency = null,
        ?string $culture = null,
    ): ?string {
        if (null === $amount) {
            return null;
        }

        $culture ??= Context::getInstance()->getUser()->getCulture();

        return (new sfNumberFormat($culture))->format(
            $amount,
            'c',
            $currency,
        );
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text(
        mixed $text,
        int $length = 30,
        string $suffix = '...',
        bool $lastSpace = false,
    ): string {
        $text = (string) $text;

        if (mb_strlen($text) <= $length) {
            return $text;
        }

        $truncated = mb_substr(
            $text,
            0,
            max(0, $length - mb_strlen($suffix)),
        );

        if ($lastSpace && false !== $space = mb_strrpos($truncated, ' ')) {
            $truncated = mb_substr($truncated, 0, $space);
        }

        return $truncated.$suffix;
    }
}

if (!function_exists('wrap_text')) {
    function wrap_text(mixed $text, int $width = 80): string
    {
        return wordwrap((string) $text, $width, "\n", true);
    }
}
