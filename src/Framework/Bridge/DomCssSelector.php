<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Bridge;

use Symfony\Component\CssSelector\CssSelectorConverter;

final class DomCssSelector implements \Countable, \Iterator
{
    public array $nodes;
    private int $position = 0;

    public function __construct(
        array|\DOMNode $nodes,
        private readonly CssSelectorConverter $converter = new CssSelectorConverter(),
    ) {
        $this->nodes = is_array($nodes) ? array_values($nodes) : [$nodes];
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function getNode(): ?\DOMNode
    {
        return $this->nodes[0] ?? null;
    }

    public function getValue(): ?string
    {
        return $this->getNode()?->nodeValue;
    }

    public function getValues(): array
    {
        return array_map(
            static fn (\DOMNode $node): ?string => $node->nodeValue,
            $this->nodes,
        );
    }

    public function matchSingle(string $selector): self
    {
        $nodes = $this->elements($selector);

        return new self(
            [] === $nodes ? [] : $nodes[0],
            $this->converter,
        );
    }

    public function matchAll(string $selector): self
    {
        return new self(
            $this->elements($selector),
            $this->converter,
        );
    }

    public function count(): int
    {
        return count($this->nodes);
    }

    public function current(): mixed
    {
        return $this->nodes[$this->position] ?? null;
    }

    public function key(): int
    {
        return $this->position;
    }

    public function next(): void
    {
        ++$this->position;
    }

    public function rewind(): void
    {
        $this->position = 0;
    }

    public function valid(): bool
    {
        return isset($this->nodes[$this->position]);
    }

    private function elements(string $selector): array
    {
        $expression = $this->converter->toXPath($selector);
        $matches = [];

        foreach ($this->nodes as $node) {
            $document = $node instanceof \DOMDocument
                ? $node
                : $node->ownerDocument;

            if (null === $document) {
                continue;
            }

            $xpath = new \DOMXPath($document);
            $result = $node instanceof \DOMDocument
                ? $xpath->query($expression)
                : $xpath->query($expression, $node);

            if (false === $result) {
                continue;
            }

            foreach ($result as $match) {
                $matches[spl_object_id($match)] = $match;
            }
        }

        return array_values($matches);
    }
}
