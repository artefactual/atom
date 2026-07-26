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

namespace Atom\Framework\Utility;

use Symfony\Component\Finder\Finder as SymfonyFinder;

final class Finder
{
    private string $type = 'file';
    private array $names = [];
    private array $notNames = [];
    private array $prunes = [];
    private ?int $maxDepth = null;
    private ?int $minDepth = null;
    private bool $relative = false;
    private bool $followLinks = false;
    private bool $sortByName = false;
    private bool $ignoreVersionControl = true;
    private bool $ignoreDotFiles = true;

    public static function type(string $type): self
    {
        return (new self())->setType($type);
    }

    public function setType(string $type): self
    {
        $type = strtolower($type);
        $this->type = str_starts_with($type, 'dir')
            ? 'directory'
            : ('any' === $type ? 'any' : 'file');

        return $this;
    }

    public function maxdepth(int $depth): self
    {
        $this->maxDepth = $depth;

        return $this;
    }

    public function mindepth(int $depth): self
    {
        $this->minDepth = $depth;

        return $this;
    }

    public function name(array|string ...$patterns): self
    {
        array_push($this->names, ...$this->flatten($patterns));

        return $this;
    }

    public function not_name(array|string ...$patterns): self
    {
        array_push($this->notNames, ...$this->flatten($patterns));

        return $this;
    }

    public function prune(array|string ...$patterns): self
    {
        array_push($this->prunes, ...$this->flatten($patterns));

        return $this;
    }

    public function discard(array|string ...$patterns): self
    {
        return $this->not_name(...$patterns);
    }

    public function ignore_version_control(bool $ignore = true): self
    {
        $this->ignoreVersionControl = $ignore;

        return $this;
    }

    public function ignore_dot_files(bool $ignore = true): self
    {
        $this->ignoreDotFiles = $ignore;

        return $this;
    }

    public function follow_link(): self
    {
        $this->followLinks = true;

        return $this;
    }

    public function relative(): self
    {
        $this->relative = true;

        return $this;
    }

    public function sort_by_name(): self
    {
        $this->sortByName = true;

        return $this;
    }

    public function in(array|string $directories): array
    {
        $directories = array_values(array_filter(
            (array) $directories,
            static fn (mixed $directory): bool => is_string($directory)
                && is_dir($directory),
        ));

        if ([] === $directories) {
            return [];
        }

        $finder = new SymfonyFinder();

        match ($this->type) {
            'directory' => $finder->directories(),
            'file' => $finder->files(),
            default => null,
        };

        foreach ($this->names as $name) {
            $finder->name($name);
        }

        foreach ($this->notNames as $name) {
            $finder->notName($name);
        }

        if ([] !== $this->prunes) {
            $finder->exclude($this->prunes);
        }

        if (null !== $this->maxDepth) {
            $finder->depth('<='.$this->maxDepth);
        }

        if (null !== $this->minDepth) {
            $finder->depth('>='.$this->minDepth);
        }

        $finder->ignoreVCS($this->ignoreVersionControl);
        $finder->ignoreDotFiles($this->ignoreDotFiles);

        if ($this->followLinks) {
            $finder->followLinks();
        }

        if ($this->sortByName) {
            $finder->sortByName();
        }

        $finder->in($directories);
        $paths = [];

        foreach ($finder as $file) {
            $paths[] = $this->relative
                ? $file->getRelativePathname()
                : $file->getPathname();
        }

        return $paths;
    }

    private function flatten(array $values): array
    {
        $flattened = [];

        array_walk_recursive(
            $values,
            static function (mixed $value) use (&$flattened): void {
                if (is_string($value)) {
                    $flattened[] = $value;
                }
            },
        );

        return $flattened;
    }
}
