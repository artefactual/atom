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

namespace Atom\Framework\Console;

use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class Formatter
{
    private int $maxLineSize;
    private OutputInterface $output;

    public function __construct(?int $maxLineSize = null)
    {
        $this->maxLineSize = $maxLineSize ?? 78;
        $this->output = new NullOutput();
    }

    public function setOutput(OutputInterface $output): void
    {
        $this->output = $output;
    }

    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    public function setStyle(string $name, array $options = []): void {}

    public function format(
        string $text = '',
        array|string|null $parameters = [],
    ): string {
        return $text;
    }

    public function formatSection(
        string $section,
        string $text,
        ?int $size = null,
        string $style = 'INFO',
    ): string {
        $prefix = sprintf('>> %-9s ', $section);

        return $prefix.$this->excerpt(
            $text,
            ($size ?? $this->maxLineSize) - strlen($prefix),
        );
    }

    public function excerpt(string $text, ?int $size = null): string
    {
        $size ??= $this->maxLineSize;

        if (strlen($text) <= $size) {
            return $text;
        }

        $half = max(0, (int) floor(($size - 3) / 2));

        return substr($text, 0, $half).'...'.substr($text, -$half);
    }

    public function setMaxLineSize(int $size): void
    {
        $this->maxLineSize = $size;
    }
}
