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

final class ResponseAdapter
{
    private string $title = '';
    private array $javaScripts = [];
    private array $stylesheets = [];
    private array $metadata = [];

    public function __construct(private readonly Response $response) {}

    public function setContent(?string $content): void
    {
        $this->response->setContent($content);
    }

    public function getContent(): false|string
    {
        return $this->response->getContent();
    }

    public function setStatusCode(int $statusCode): void
    {
        $this->response->setStatusCode($statusCode);
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

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function addJavaScript(
        string $path,
        string $position = '',
    ): void {
        $this->javaScripts[$position][] = $path;
    }

    public function addStylesheet(
        string $path,
        string $position = '',
    ): void {
        $this->stylesheets[$position][] = $path;
    }

    public function addMeta(string $key, string $value): void
    {
        $this->metadata[$key] = $value;
    }

    public function getSymfonyResponse(): Response
    {
        return $this->response;
    }
}
