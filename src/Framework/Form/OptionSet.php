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

abstract class OptionSet
{
    protected array $options = [];
    private array $requiredOptions = [];

    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return array_key_exists($name, $this->options)
            ? $this->options[$name]
            : $default;
    }

    public function hasOption(string $name): bool
    {
        return array_key_exists($name, $this->options)
            && null !== $this->options[$name];
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    protected function addOption(string $name, mixed $default = null): void
    {
        if (!array_key_exists($name, $this->options)) {
            $this->options[$name] = $default;
        }
    }

    protected function addRequiredOption(string $name): void
    {
        $this->requiredOptions[$name] = true;
        $this->options[$name] ??= null;
    }

    protected function applyOptions(array $options): void
    {
        foreach ($options as $name => $value) {
            $this->setOption((string) $name, $value);
        }

        foreach (array_keys($this->requiredOptions) as $name) {
            if (null === $this->options[$name]) {
                throw new \InvalidArgumentException(sprintf(
                    'The required option "%s" is missing.',
                    $name,
                ));
            }
        }
    }
}
