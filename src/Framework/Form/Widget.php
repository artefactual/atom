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

class Widget extends OptionSet
{
    protected array $attributes = [];
    protected ?WidgetSchema $parent = null;

    public function __construct(
        array $options = [],
        array $attributes = [],
    ) {
        $this->addOption('id_format', '%s');
        $this->addOption('is_hidden', false);
        $this->addOption('needs_multipart', false);
        $this->addOption('default');
        $this->addOption('label');
        $this->addOption('help');
        $this->configure($options, $attributes);
        $this->applyOptions($options);
        $this->attributes = $attributes;
    }

    public function render(
        string $name,
        mixed $value = null,
        array $attributes = [],
        array|ValidatorError|null $errors = [],
    ) {
        return $this->renderTag('input', array_replace(
            ['type' => $this->getOption('type', 'text'), 'name' => $name],
            $this->attributes,
            $attributes,
            null === $value ? [] : ['value' => $value],
        ));
    }

    public function setDefault(mixed $value): static
    {
        return $this->setOption('default', $value);
    }

    public function getDefault(): mixed
    {
        return $this->getOption('default');
    }

    public function setLabel(
        false|string|null $value,
        false|string|null $label = null,
    ): static {
        if (1 < func_num_args()) {
            $this->setFieldLabel((string) $value, $label);

            return $this;
        }

        return $this->setOption('label', $value);
    }

    public function getLabel(?string $name = null): false|string|null
    {
        if (null !== $name) {
            return $this->getFieldLabel($name);
        }

        return $this->getOption('label');
    }

    public function setHelp(
        ?string $value,
        ?string $help = null,
    ): static {
        if (1 < func_num_args()) {
            $this->setFieldHelp((string) $value, $help);

            return $this;
        }

        return $this->setOption('help', $value);
    }

    public function getHelp(?string $name = null): ?string
    {
        if (null !== $name) {
            return $this->getFieldHelp($name);
        }

        return $this->getOption('help');
    }

    public function setIdFormat(false|string $format): static
    {
        return $this->setOption('id_format', $format);
    }

    public function getIdFormat(): false|string
    {
        return $this->getOption('id_format');
    }

    public function isHidden(): bool
    {
        return (bool) $this->getOption('is_hidden');
    }

    public function setHidden(bool $hidden): static
    {
        return $this->setOption('is_hidden', $hidden);
    }

    public function needsMultipartForm(): bool
    {
        return (bool) $this->getOption('needs_multipart');
    }

    public function setAttribute(string $name, mixed $value): static
    {
        $this->attributes[$name] = $value;

        return $this;
    }

    public function getAttribute(string $name, mixed $default = null): mixed
    {
        return $this->attributes[$name] ?? $default;
    }

    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function setParent(?WidgetSchema $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?WidgetSchema
    {
        return $this->parent;
    }

    public function generateId(string $name, mixed $value = null): ?string
    {
        if (false === $this->getOption('id_format')) {
            return null;
        }

        if (str_contains($name, '[')) {
            $suffix = null !== $value && !is_array($value)
                ? '_'.$value
                : '';
            $name = str_replace(
                ['[]', '][', '[', ']'],
                [$suffix, '_', '_', ''],
                $name,
            );
        }

        $format = (string) $this->getOption('id_format');

        if (str_contains($format, '%s')) {
            $name = sprintf($format, $name);
        }

        return preg_replace(
            ['/^[^A-Za-z]+/', '/[^A-Za-z0-9:_\.\-]/'],
            ['', '_'],
            $name,
        );
    }

    public function renderTag(string $tag, array $attributes = []): string
    {
        if ('' === $tag) {
            return '';
        }

        if (
            !array_key_exists('id', $attributes)
            && isset($attributes['name'])
        ) {
            $attributes['id'] = $this->generateId(
                (string) $attributes['name'],
                $attributes['value'] ?? null,
            );
        }

        return '<'.$tag.$this->attributesToHtml($attributes).' />';
    }

    public function renderContentTag(
        string $tag,
        mixed $content = null,
        array $attributes = [],
    ): string {
        if (
            !array_key_exists('id', $attributes)
            && isset($attributes['name'])
        ) {
            $attributes['id'] = $this->generateId(
                (string) $attributes['name'],
            );
        }

        return '<'.$tag.$this->attributesToHtml($attributes).'>'
            .$content.'</'.$tag.'>';
    }

    public function attributesToHtml(array $attributes): string
    {
        $html = '';

        foreach ($attributes as $name => $value) {
            if (false === $value || null === $value) {
                continue;
            }

            if (true === $value) {
                $value = $name;
            } elseif (is_array($value)) {
                $value = implode(' ', $value);
            }

            $html .= sprintf(
                ' %s="%s"',
                htmlspecialchars((string) $name, \ENT_QUOTES),
                htmlspecialchars(
                    (string) $value,
                    \ENT_QUOTES | \ENT_SUBSTITUTE,
                    'UTF-8',
                ),
            );
        }

        return $html;
    }

    public function getStylesheets(): array
    {
        return [];
    }

    public function getJavaScripts(): array
    {
        return [];
    }

    protected function configure(
        array $options = [],
        array $attributes = [],
    ) {}

    protected function translate(
        string $subject,
        array $parameters = [],
    ): string {
        if (
            function_exists('__')
            && \Atom\Framework\Bridge\Context::hasInstance()
        ) {
            return (string) __($subject, $parameters);
        }

        return strtr($subject, $parameters);
    }

    protected function setFieldLabel(
        string $name,
        false|string|null $label,
    ): void {
        throw new \LogicException('This widget does not contain fields.');
    }

    protected function getFieldLabel(string $name): false|string|null
    {
        throw new \LogicException('This widget does not contain fields.');
    }

    protected function setFieldHelp(string $name, ?string $help): void
    {
        throw new \LogicException('This widget does not contain fields.');
    }

    protected function getFieldHelp(string $name): ?string
    {
        throw new \LogicException('This widget does not contain fields.');
    }
}
