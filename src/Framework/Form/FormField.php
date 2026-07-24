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

class FormField implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $optionOverrides = [];

    public function __construct(
        private Widget $widget,
        private WidgetSchema $parent,
        private string $name,
        private mixed $value,
        private ?ValidatorError $error = null,
    ) {}

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $exception) {
            return 'Exception: '.$exception->getMessage();
        }
    }

    public function __call(string $name, array $arguments): static
    {
        $clone = clone $this;
        $clone->widget = clone $this->widget;
        $value = $arguments[0] ?? null;
        $clone->widget->setOption($name, $value);
        $clone->optionOverrides[$name] = $value;

        return $clone;
    }

    public function __isset(string $name): bool
    {
        return $this->widget->hasOption($name);
    }

    public function __get(string $name): mixed
    {
        return $this->widget->getOption($name);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->widget->setOption($name, $value);
    }

    public function __unset(string $name): void
    {
        $this->widget->setOption($name, null);
    }

    public function render(array $attributes = []): string
    {
        if ($this->widget instanceof WidgetSchema) {
            return $this->widget->render(
                $this->renderName(),
                $this->value,
                $attributes,
                $this->error,
            );
        }

        return $this->widget->render(
            $this->renderName(),
            $this->value,
            $attributes,
            $this->error,
        );
    }

    public function renderRow(
        array $attributes = [],
        ?string $label = null,
        ?string $help = null,
    ): string {
        $error = $this->error instanceof ValidatorErrorSchema
            ? $this->error->getGlobalErrors()
            : $this->error;

        return str_replace(
            '%hidden_fields%',
            '',
            $this->parent->getFormFormatter()->formatRow(
                $this->renderLabel($label),
                $this->render($attributes),
                $error,
                $help ?? $this->effectiveHelp(),
            ),
        );
    }

    public function renderError(): string
    {
        $error = $this->widget instanceof WidgetSchema
            ? $this->widget->getGlobalErrors($this->error)
            : $this->error;

        return $this->parent->getFormFormatter()
            ->formatErrorsForRow($error);
    }

    public function renderHelp(): string
    {
        return $this->parent->getFormFormatter()
            ->formatHelp($this->effectiveHelp());
    }

    public function renderLabel(
        ?string $label = null,
        array $attributes = [],
    ): string {
        $label ??= $this->optionOverrides['label'] ?? null;
        $current = $this->parent->getLabel($this->name);

        if (null !== $label) {
            $this->parent->setLabel($this->name, $label);
        }

        try {
            return $this->parent->getFormFormatter()->generateLabel(
                $this->name,
                $attributes,
            );
        } finally {
            if (null !== $label) {
                $this->parent->setLabel($this->name, $current);
            }
        }
    }

    public function renderLabelName(): false|string
    {
        return $this->parent->getFormFormatter()
            ->generateLabelName($this->name);
    }

    public function renderName(): string
    {
        return $this->parent->generateName($this->name);
    }

    public function renderId(): ?string
    {
        return $this->widget->generateId($this->renderName(), $this->value);
    }

    public function isHidden(): bool
    {
        return $this->widget->isHidden();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getWidget(): Widget
    {
        return $this->widget;
    }

    public function getParent(): WidgetSchema
    {
        return $this->parent;
    }

    public function getError(): ?ValidatorError
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return null !== $this->error;
    }

    public function count(): int
    {
        return $this->widget instanceof WidgetSchema
            ? count($this->widget)
            : 0;
    }

    public function getIterator(): \Traversable
    {
        if (!$this->widget instanceof WidgetSchema) {
            return;
        }

        foreach ($this->widget->getPositions() as $name) {
            yield $name => $this->child($name);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->widget instanceof WidgetSchema
            && isset($this->widget[$offset]);
    }

    public function offsetGet(mixed $offset): ?self
    {
        return $this->offsetExists($offset)
            ? $this->child((string) $offset)
            : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Cannot update bound form fields.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \LogicException('Cannot update bound form fields.');
    }

    private function effectiveHelp(): ?string
    {
        return $this->optionOverrides['help']
            ?? $this->parent->getHelp($this->name);
    }

    private function child(string $name): self
    {
        $error = $this->error instanceof ValidatorErrorSchema
            ? $this->error[$name]
            : null;

        return new self(
            $this->widget[$name],
            $this->widget,
            $name,
            is_array($this->value) ? ($this->value[$name] ?? null) : null,
            $error,
        );
    }
}
