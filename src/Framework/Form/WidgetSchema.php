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

class WidgetSchema extends Widget implements \ArrayAccess, \Countable, \IteratorAggregate
{
    private array $fields = [];
    private array $positions = [];
    private array $labels = [];
    private array $helps = [];
    private array $formatters = [];
    private string $formatterName = 'default';
    private string $nameFormat = '%s';

    public function __construct(array $fields = [])
    {
        parent::__construct();
        $this->formatters['default'] = new SchemaFormatter($this);

        foreach ($fields as $name => $widget) {
            $this[$name] = $widget;
        }
    }

    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    public function __get(string $name): ?Widget
    {
        return $this->offsetGet($name);
    }

    public function __set(string $name, Widget $widget): void
    {
        $this->offsetSet($name, $widget);
    }

    public function __clone()
    {
        foreach ($this->fields as $name => $widget) {
            $this->fields[$name] = clone $widget;
            $this->fields[$name]->setParent($this);
        }

        foreach ($this->formatters as $name => $formatter) {
            $this->formatters[$name] = clone $formatter;
            $this->formatters[$name]->setWidgetSchema($this);
        }
    }

    public function render(
        string $name = '',
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ): string {
        $errorSchema = $errors instanceof ValidatorErrorSchema
            ? $errors
            : null;
        $html = '';
        $hidden = '';

        foreach ($this->positions as $fieldName) {
            $field = new FormField(
                $this->fields[$fieldName],
                $this,
                $fieldName,
                is_array($value) ? ($value[$fieldName] ?? null) : null,
                $errorSchema?->offsetGet($fieldName),
            );

            if ($field->isHidden()) {
                $hidden .= $field->render();
            } else {
                $html .= $field->renderRow();
            }
        }

        $html = str_replace('%hidden_fields%', $hidden, $html);

        return strtr($this->getFormFormatter()->getDecoratorFormat(), [
            '%content%' => $html,
        ]);
    }

    public function renderField(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $error = null,
    ): string {
        $widget = $this->fields[$name] ?? throw new \InvalidArgumentException(
            sprintf('Widget "%s" does not exist.', $name),
        );

        return $widget->render(
            $this->generateName($name),
            $value,
            $attributes,
            $error,
        );
    }

    public function setNameFormat(string $format): static
    {
        if (!str_contains($format, '%s')) {
            throw new \InvalidArgumentException(
                'A form name format must contain "%s".',
            );
        }

        $old = $this->nameFormat;
        $this->nameFormat = $format;

        foreach ($this->fields as $name => $widget) {
            if ($widget instanceof self) {
                $expected = sprintf($old, $name).'[%s]';

                if ($expected === $widget->getNameFormat()) {
                    $widget->setNameFormat(
                        $this->generateName((string) $name).'[%s]',
                    );
                }
            }
        }

        return $this;
    }

    public function getNameFormat(): string
    {
        return $this->nameFormat;
    }

    public function generateName(string $name): string
    {
        return sprintf($this->nameFormat, $name);
    }

    public function setIdFormat(false|string $format): static
    {
        parent::setIdFormat($format);

        foreach ($this->fields as $widget) {
            $widget->setIdFormat($format);
        }

        return $this;
    }

    public function setLabels(array $labels): static
    {
        foreach ($labels as $name => $label) {
            $this->setLabel((string) $name, $label);
        }

        return $this;
    }

    public function getLabels(): array
    {
        return $this->labels;
    }

    public function setHelps(array $helps): static
    {
        foreach ($helps as $name => $help) {
            $this->setHelp((string) $name, $help);
        }

        return $this;
    }

    public function getHelps(): array
    {
        return $this->helps;
    }

    public function addFormFormatter(
        int|string $name,
        SchemaFormatter $formatter,
    ): static {
        $formatter->setWidgetSchema($this);
        $this->formatters[(string) $name] = $formatter;

        return $this;
    }

    public function setFormFormatterName(int|string $name): static
    {
        if (!isset($this->formatters[(string) $name])) {
            throw new \InvalidArgumentException(sprintf(
                'Form formatter "%s" does not exist.',
                $name,
            ));
        }

        $this->formatterName = (string) $name;

        return $this;
    }

    public function getFormFormatterName(): string
    {
        return $this->formatterName;
    }

    public function getFormFormatter(): SchemaFormatter
    {
        return $this->formatters[$this->formatterName];
    }

    public function getGlobalErrors(
        ?ValidatorError $errors,
    ): array {
        if (!$errors instanceof ValidatorErrorSchema) {
            return null === $errors ? [] : [$errors];
        }

        $global = $errors->getGlobalErrors();

        foreach ($errors->getNamedErrors() as $name => $error) {
            if (!isset($this->fields[$name]) || '_csrf_token' === $name) {
                $global[$name] = $error;
            }
        }

        return $global;
    }

    public function needsMultipartForm(): bool
    {
        foreach ($this->fields as $widget) {
            if ($widget->needsMultipartForm()) {
                return true;
            }
        }

        return false;
    }

    public function getStylesheets(): array
    {
        return array_merge(
            ...array_map(
                static fn (Widget $widget): array => $widget->getStylesheets(),
                $this->fields,
            ),
        );
    }

    public function getJavaScripts(): array
    {
        return array_merge(
            ...array_map(
                static fn (Widget $widget): array => $widget->getJavaScripts(),
                $this->fields,
            ),
        );
    }

    public function setPositions(array $positions): static
    {
        if ([] !== array_diff($positions, array_keys($this->fields))) {
            throw new \InvalidArgumentException(
                'Widget positions contain an unknown field.',
            );
        }

        $this->positions = array_values($positions);

        return $this;
    }

    public function getPositions(): array
    {
        return $this->positions;
    }

    public function count(): int
    {
        return count($this->fields);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->positions as $name) {
            yield $name => $this->fields[$name];
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fields[$offset]);
    }

    public function offsetGet(mixed $offset): ?Widget
    {
        return $this->fields[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Widget) {
            throw new \InvalidArgumentException(
                'A schema field must be a Widget.',
            );
        }

        $name = (string) $offset;
        $this->fields[$name] = clone $value;
        $this->fields[$name]->setParent($this);

        if (!in_array($name, $this->positions, true)) {
            $this->positions[] = $name;
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->fields[$offset]);
        $this->positions = array_values(array_filter(
            $this->positions,
            static fn (string $name): bool => $name !== $offset,
        ));
    }

    protected function setFieldLabel(
        string $name,
        false|string|null $label,
    ): void {
        $this->labels[$name] = $label;
    }

    protected function getFieldLabel(string $name): false|string|null
    {
        return array_key_exists($name, $this->labels)
            ? $this->labels[$name]
            : ($this->fields[$name] ?? null)?->getLabel();
    }

    protected function setFieldHelp(string $name, ?string $help): void
    {
        $this->helps[$name] = $help;
    }

    protected function getFieldHelp(string $name): ?string
    {
        return array_key_exists($name, $this->helps)
            ? $this->helps[$name]
            : ($this->fields[$name] ?? null)?->getHelp();
    }
}
