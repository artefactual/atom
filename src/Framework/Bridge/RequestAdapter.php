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

use Symfony\Component\HttpFoundation\Request;

final class RequestAdapter implements \ArrayAccess
{
    private ParameterHolder $parameters;

    public function __construct(private readonly Request $request)
    {
        $attributes = array_filter(
            $request->attributes->all(),
            static fn (string $name): bool => !str_starts_with($name, '_'),
            \ARRAY_FILTER_USE_KEY,
        );
        $this->parameters = new ParameterHolder(array_replace(
            $attributes,
            $request->query->all(),
            $request->request->all(),
        ));
    }

    public function __get(string $name): mixed
    {
        return $this->getParameter($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->setParameter($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->hasParameter($name);
    }

    public function __unset(string $name): void
    {
        $this->parameters->remove($name);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset((string) $offset);
    }

    public function getParameter(
        int|string $name,
        mixed $default = null,
    ): mixed {
        return $this->parameters->get($name, $default);
    }

    public function setParameter(int|string $name, mixed $value): void
    {
        $this->parameters->set($name, $value);
    }

    public function hasParameter(int|string $name): bool
    {
        return $this->parameters->has($name);
    }

    public function getParameterHolder(): ParameterHolder
    {
        return $this->parameters;
    }

    public function getRequestParameters(): array
    {
        return $this->parameters->getAll();
    }

    public function getGetParameters(): array
    {
        return $this->request->query->all();
    }

    public function getGetParameter(string $name, mixed $default = null): mixed
    {
        return $this->request->query->get($name, $default);
    }

    public function getPostParameters(): array
    {
        return $this->request->request->all();
    }

    public function getPostParameter(
        string $name,
        mixed $default = null,
    ): mixed {
        return $this->request->request->get($name, $default);
    }

    public function getMethod(): string
    {
        return strtolower($this->request->getMethod());
    }

    public function isMethod(string $method): bool
    {
        return $this->request->isMethod($method);
    }

    public function getHttpHeader(
        string $name,
        mixed $default = null,
    ): mixed {
        return $this->request->headers->get($name, $default);
    }

    public function getReferer(): ?string
    {
        return $this->request->headers->get('referer');
    }

    public function getCookie(string $name, mixed $default = null): mixed
    {
        return $this->request->cookies->get($name, $default);
    }

    public function getFiles(): array
    {
        return $this->request->files->all();
    }

    public function getFile(string $name, mixed $default = null): mixed
    {
        return $this->request->files->get($name, $default);
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->request->attributes->get($name, $default);
    }

    public function setAttribute(string $name, mixed $value): void
    {
        $this->request->attributes->set($name, $value);
    }

    public function getPathInfo(): string
    {
        return $this->request->getPathInfo();
    }

    public function getUri(): string
    {
        return $this->request->getUri();
    }

    public function getUriPrefix(): string
    {
        return $this->request->getSchemeAndHttpHost();
    }

    public function getScriptName(): string
    {
        return $this->request->getScriptName();
    }

    public function getRelativeUrlRoot(): string
    {
        return $this->request->getBaseUrl();
    }

    public function isXmlHttpRequest(): bool
    {
        return $this->request->isXmlHttpRequest();
    }

    public function setRequestFormat(string $format): void
    {
        $this->request->setRequestFormat($format);
    }

    public function getRequestFormat(?string $default = 'html'): ?string
    {
        return $this->request->getRequestFormat($default);
    }

    public function getSymfonyRequest(): Request
    {
        return $this->request;
    }
}
