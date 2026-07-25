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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

use Symfony\Component\HttpFoundation\Response;

class ResponseAdapter
{
    private string $title = '';
    private array $javaScripts = [];
    private array $stylesheets = [];
    private array $httpMetadata = [];
    private array $metadata = [];
    private bool $headerOnly = false;

    public function __construct(private readonly Response $response) {}

    public function setContent(?string $content): void
    {
        $this->response->setContent($content);
    }

    public function getContent(): false|string
    {
        return $this->response->getContent();
    }

    public function setStatusCode(
        int $statusCode,
        ?string $statusText = null,
    ): void {
        $this->response->setStatusCode($statusCode, $statusText);
    }

    public function setHttpHeader(
        string $name,
        string $value,
        bool $replace = true,
    ): void {
        $this->response->headers->set($name, $value, $replace);
    }

    public function setContentType(string $contentType): void
    {
        $this->response->headers->set('Content-Type', $contentType);
    }

    public function clearHttpHeaders(): void
    {
        $this->response->headers->replace();
    }

    public function sendHttpHeaders(): void
    {
        $this->response->sendHeaders();
    }

    public function setHeaderOnly(bool $headerOnly): void
    {
        $this->headerOnly = $headerOnly;
    }

    public function isHeaderOnly(): bool
    {
        return $this->headerOnly;
    }

    public function setTitle(string $title): void
    {
        if (class_exists('\\QubitMarkdown')) {
            $title = \QubitMarkdown::getInstance()->strip($title);
        }

        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function addJavaScript(
        string $path,
        string $position = '',
        array $options = [],
    ): void {
        $this->addAsset(
            $this->javaScripts,
            $path,
            $position,
            $options,
        );
    }

    public function addStylesheet(
        string $path,
        string $position = '',
        array $options = [],
    ): void {
        $this->addAsset(
            $this->stylesheets,
            $path,
            $position,
            $options,
        );
    }

    public function getJavascripts(): array
    {
        return $this->orderedAssets($this->javaScripts);
    }

    public function getStylesheets(): array
    {
        return $this->orderedAssets($this->stylesheets);
    }

    public function addMeta(string $key, string $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getMetas(): array
    {
        return $this->metadata;
    }

    public function addHttpMeta(string $key, string $value): void
    {
        $this->httpMetadata[$key] = $value;
    }

    public function getHttpMetas(): array
    {
        return $this->httpMetadata;
    }

    public function getSymfonyResponse(): Response
    {
        return $this->response;
    }

    private function addAsset(
        array &$assets,
        string $path,
        string $position,
        array $options,
    ): void {
        if (isset($options['position'])) {
            $position = (string) $options['position'];
            unset($options['position']);
        }

        foreach ($assets as &$positionAssets) {
            unset($positionAssets[$path]);
        }
        unset($positionAssets);

        $assets[$position][$path] = $options;
    }

    private function orderedAssets(array $assets): array
    {
        $ordered = [];

        foreach (['first', '', 'last'] as $position) {
            foreach ($assets[$position] ?? [] as $path => $options) {
                $ordered[$path] = $options;
            }
        }

        foreach (array_diff(array_keys($assets), ['first', '', 'last']) as $position) {
            foreach ($assets[$position] as $path => $options) {
                $ordered[$path] = $options;
            }
        }

        return $ordered;
    }
}
