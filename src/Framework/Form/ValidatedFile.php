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

namespace Atom\Framework\Form;

class ValidatedFile
{
    protected ?string $savedName = null;

    public function __construct(
        protected string $originalName,
        protected string $type,
        protected string $tempName,
        protected int $size,
        protected ?string $path = null,
    ) {}

    public function __toString(): string
    {
        return $this->savedName ?? '';
    }

    public function save(
        ?string $file = null,
        int $fileMode = 0666,
        bool $create = true,
        int $dirMode = 0777,
    ) {
        $file ??= $this->generateFilename();

        if (!$this->isAbsolute($file)) {
            if (null === $this->path) {
                throw new \RuntimeException(
                    'A path is required for a relative file name.',
                );
            }

            $file = $this->path.\DIRECTORY_SEPARATOR.$file;
        }

        $directory = dirname($file);

        if (
            !is_dir($directory)
            && (!$create || !mkdir($directory, $dirMode, true))
        ) {
            throw new \RuntimeException(sprintf(
                'Failed to create upload directory "%s".',
                $directory,
            ));
        }

        if (!is_writable($directory) || !copy($this->tempName, $file)) {
            throw new \RuntimeException(sprintf(
                'Failed to save uploaded file to "%s".',
                $file,
            ));
        }

        chmod($file, $fileMode);
        $this->savedName = $file;

        return null === $this->path
            ? $file
            : ltrim(substr($file, strlen($this->path)), '/\\');
    }

    public function generateFilename(): string
    {
        return bin2hex(random_bytes(20)).$this->getOriginalExtension();
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getExtension(string $default = ''): string
    {
        $extension = match ($this->type) {
            'image/jpeg' => '.jpg',
            'image/png' => '.png',
            'image/gif' => '.gif',
            'application/pdf' => '.pdf',
            'application/rtf', 'text/rtf' => '.rtf',
            'text/plain' => '.txt',
            default => '',
        };

        return '' === $extension ? $default : $extension;
    }

    public function getOriginalExtension(string $default = ''): string
    {
        $extension = pathinfo($this->originalName, \PATHINFO_EXTENSION);

        return '' === $extension ? $default : '.'.$extension;
    }

    public function isSaved(): bool
    {
        return null !== $this->savedName;
    }

    public function getSavedName(): ?string
    {
        return $this->savedName;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getTempName(): string
    {
        return $this->tempName;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    private function isAbsolute(string $path): bool
    {
        return str_starts_with($path, '/')
            || str_starts_with($path, '\\')
            || 1 === preg_match('/^[A-Za-z]:[\\\\\\/]/', $path);
    }
}
