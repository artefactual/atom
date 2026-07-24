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

use Atom\Framework\Bridge\Configuration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class Form implements \ArrayAccess, \Countable, \IteratorAggregate
{
    protected static string $CSRFFieldName = '_csrf_token';
    protected static bool|string|null $CSRFSecret = null;
    protected WidgetSchema $widgetSchema;
    protected SchemaValidator $validatorSchema;
    protected ValidatorErrorSchema $errorSchema;
    protected array $defaults = [];
    protected array $options = [];
    protected array $embeddedForms = [];
    protected array $taintedValues = [];
    protected array $taintedFiles = [];
    protected array $values = [];
    protected bool $isBound = false;
    protected bool|string|null $localCSRFSecret;
    private ?CsrfTokenManagerInterface $csrfManager = null;

    public function __construct(
        ?array $defaults = [],
        array $options = [],
        bool|string|null $CSRFSecret = null,
    ) {
        $this->defaults = $defaults ?? [];
        $this->options = $options;
        $this->localCSRFSecret = $CSRFSecret;
        $this->validatorSchema = new SchemaValidator();
        $this->widgetSchema = new WidgetSchema();
        $this->errorSchema = new ValidatorErrorSchema(
            $this->validatorSchema,
        );
        $this->installFormatter();
        $this->setup();
        $this->configure();
        $this->addCSRFProtection();
    }

    public function __toString(): string
    {
        try {
            return $this->render();
        } catch (\Throwable $exception) {
            return 'Exception: '.$exception->getMessage();
        }
    }

    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    public function __get(string $name): FormField
    {
        return $this->field($name);
    }

    public function __unset(string $name): void
    {
        $this->offsetUnset($name);
    }

    public function __clone()
    {
        $this->widgetSchema = clone $this->widgetSchema;
        $this->validatorSchema = clone $this->validatorSchema;
        $this->errorSchema = new ValidatorErrorSchema(
            $this->validatorSchema,
        );
    }

    public function setup() {}

    public function configure() {}

    public function render(array $attributes = []): string
    {
        $html = '';
        $hidden = '';

        foreach ($this as $field) {
            if ($field->isHidden()) {
                $hidden .= $field->render();
            } else {
                $html .= $field->renderRow($attributes);
            }
        }

        $html = str_replace('%hidden_fields%', $hidden, $html);

        return strtr(
            $this->widgetSchema->getFormFormatter()
                ->getDecoratorFormat(),
            ['%content%' => $html],
        );
    }

    public function renderUsing(
        string $formatterName,
        array $attributes = [],
    ): string {
        $current = $this->widgetSchema->getFormFormatterName();
        $this->widgetSchema->setFormFormatterName($formatterName);

        try {
            return $this->render($attributes);
        } finally {
            $this->widgetSchema->setFormFormatterName($current);
        }
    }

    public function renderHiddenFields(bool $recursive = true): string
    {
        $html = '';

        foreach ($this as $field) {
            if ($field->isHidden()) {
                $html .= $field->render();
            } elseif (
                $recursive
                && $field->getWidget() instanceof WidgetSchema
            ) {
                foreach ($field as $child) {
                    if ($child->isHidden()) {
                        $html .= $child->render();
                    }
                }
            }
        }

        return $html;
    }

    public function renderGlobalErrors(): string
    {
        return $this->widgetSchema->getFormFormatter()
            ->formatErrorsForRow($this->getGlobalErrors());
    }

    public function hasGlobalErrors(): bool
    {
        return [] !== $this->getGlobalErrors();
    }

    public function getGlobalErrors(): array
    {
        return $this->widgetSchema->getGlobalErrors($this->errorSchema);
    }

    public function bind(
        ?array $taintedValues = null,
        ?array $taintedFiles = null,
    ): void {
        $this->taintedValues = $taintedValues ?? [];
        $this->taintedFiles = $this->normalizeFiles(
            $taintedFiles ?? [],
        );
        $this->isBound = true;
        $submitted = $this->deepUnion(
            $this->taintedValues,
            $this->taintedFiles,
        );

        try {
            $this->values = $this->validatorSchema->clean($submitted);
            $this->errorSchema = new ValidatorErrorSchema(
                $this->validatorSchema,
            );
            unset($this->values[self::$CSRFFieldName]);
        } catch (ValidatorErrorSchema $errors) {
            $this->values = [];
            $this->errorSchema = $errors;
        }
    }

    public function isBound(): bool
    {
        return $this->isBound;
    }

    public function getTaintedValues(): array
    {
        return $this->isBound ? $this->taintedValues : [];
    }

    public function isValid(): bool
    {
        return $this->isBound && 0 === count($this->errorSchema);
    }

    public function hasErrors(): bool
    {
        return $this->isBound && 0 < count($this->errorSchema);
    }

    public function getValues(): array
    {
        return $this->isBound ? $this->values : [];
    }

    public function getValue(string $field): mixed
    {
        $value = $this->isBound && array_key_exists($field, $this->values)
            ? $this->values[$field]
            : null;

        $widget = $this->widgetSchema[$field] ?? null;
        if (
            $widget instanceof Widget
            && (bool) $widget->getOption('multiple', false)
        ) {
            if (is_array($value)) {
                return $value;
            }

            return null === $value || '' === $value ? [] : [$value];
        }

        return $value;
    }

    public function getName(): false|string
    {
        $format = $this->widgetSchema->getNameFormat();

        return str_ends_with($format, '[%s]')
            ? substr($format, 0, -4)
            : false;
    }

    public function getErrorSchema(): ValidatorErrorSchema
    {
        return $this->errorSchema;
    }

    public function embedForm(
        int|string $name,
        Form $form,
        ?string $decorator = null,
    ): void {
        if ($this->isBound || $form->isBound()) {
            throw new \LogicException('A bound form cannot be embedded.');
        }

        $name = (string) $name;
        $this->embeddedForms[$name] = $form;
        $form = clone $form;
        unset($form[self::$CSRFFieldName]);
        $schema = clone $form->getWidgetSchema();
        $schema->setNameFormat(
            $this->widgetSchema->generateName($name).'[%s]',
        );
        $this->widgetSchema[$name] = $schema;
        $this->validatorSchema[$name] = $form->getValidatorSchema();
        $this->setDefault($name, $form->getDefaults());
    }

    public function getEmbeddedForms(): array
    {
        return $this->embeddedForms;
    }

    public function getEmbeddedForm(string $name): Form
    {
        return $this->embeddedForms[$name]
            ?? throw new \InvalidArgumentException(sprintf(
                'Embedded form "%s" does not exist.',
                $name,
            ));
    }

    public function mergeForm(Form $form): void
    {
        if ($this->isBound || $form->isBound()) {
            throw new \LogicException('A bound form cannot be merged.');
        }

        foreach ($form->getWidgetSchema()->getPositions() as $name) {
            if (self::$CSRFFieldName !== $name) {
                $this->setWidget($name, $form->getWidget($name));
            }
        }

        foreach ($form->getValidatorSchema()->getFields() as $name => $item) {
            if (self::$CSRFFieldName !== $name) {
                $this->setValidator($name, $item);
            }
        }

        $this->defaults = $form->getDefaults() + $this->defaults;
        $this->widgetSchema->setLabels(
            $form->getWidgetSchema()->getLabels(),
        );
        $this->widgetSchema->setHelps(
            $form->getWidgetSchema()->getHelps(),
        );
        $this->mergePreValidator(
            $form->getValidatorSchema()->getPreValidator(),
        );
        $this->mergePostValidator(
            $form->getValidatorSchema()->getPostValidator(),
        );
    }

    public function mergePreValidator(?Validator $validator): void
    {
        if (null === $validator) {
            return;
        }

        $current = $this->validatorSchema->getPreValidator();
        $this->validatorSchema->setPreValidator(
            null === $current
                ? $validator
                : new AndValidator([$current, $validator]),
        );
    }

    public function mergePostValidator(?Validator $validator): void
    {
        if (null === $validator) {
            return;
        }

        $current = $this->validatorSchema->getPostValidator();
        $this->validatorSchema->setPostValidator(
            null === $current
                ? $validator
                : new AndValidator([$current, $validator]),
        );
    }

    public function setValidators(array $validators): static
    {
        $this->validatorSchema = new SchemaValidator($validators);
        $this->errorSchema = new ValidatorErrorSchema(
            $this->validatorSchema,
        );

        return $this;
    }

    public function setValidator(
        string $name,
        Validator $validator,
    ): static {
        $this->validatorSchema[$name] = $validator;

        return $this;
    }

    public function getValidator(string $name): Validator
    {
        return $this->validatorSchema[$name]
            ?? throw new \InvalidArgumentException(sprintf(
                'Validator "%s" does not exist.',
                $name,
            ));
    }

    public function setValidatorSchema(
        SchemaValidator $validatorSchema,
    ): static {
        $this->validatorSchema = $validatorSchema;
        $this->errorSchema = new ValidatorErrorSchema(
            $this->validatorSchema,
        );

        return $this;
    }

    public function getValidatorSchema(): SchemaValidator
    {
        return $this->validatorSchema;
    }

    public function setWidgets(array $widgets): static
    {
        foreach ($widgets as $name => $widget) {
            $this->setWidget((string) $name, $widget);
        }

        return $this;
    }

    public function setWidget(string $name, Widget $widget): static
    {
        $this->widgetSchema[$name] = $widget;

        return $this;
    }

    public function getWidget(string $name): Widget
    {
        return $this->widgetSchema[$name]
            ?? throw new \InvalidArgumentException(sprintf(
                'Widget "%s" does not exist.',
                $name,
            ));
    }

    public function setWidgetSchema(WidgetSchema $widgetSchema): static
    {
        $this->widgetSchema = $widgetSchema;

        return $this;
    }

    public function getWidgetSchema(): WidgetSchema
    {
        return $this->widgetSchema;
    }

    public function getStylesheets(): array
    {
        return $this->widgetSchema->getStylesheets();
    }

    public function getJavaScripts(): array
    {
        return $this->widgetSchema->getJavaScripts();
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOption(string $name, mixed $value): static
    {
        $this->options[$name] = $value;

        return $this;
    }

    public function getOption(string $name, mixed $default = null): mixed
    {
        return $this->options[$name] ?? $default;
    }

    public function setDefault(string $name, mixed $default): static
    {
        $this->defaults[$name] = $default;

        return $this;
    }

    public function getDefault(string $name): mixed
    {
        return $this->defaults[$name] ?? null;
    }

    public function hasDefault(string $name): bool
    {
        return array_key_exists($name, $this->defaults);
    }

    public function setDefaults(?array $defaults): static
    {
        $this->defaults = $defaults ?? [];

        if ($this->isCSRFProtected()) {
            $this->defaults[self::$CSRFFieldName] = $this->getCSRFToken();
        }

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->defaults;
    }

    public function addCSRFProtection(
        bool|string|null $secret = null,
    ): static {
        $secret ??= $this->effectiveCsrfSecret();

        if (false === $secret) {
            return $this;
        }

        $manager = $this->csrfManager();
        $tokenId = $this->csrfTokenId();
        $this->validatorSchema[self::$CSRFFieldName] =
            new CsrfTokenValidator($manager, $tokenId);
        $this->widgetSchema[self::$CSRFFieldName] =
            new InputHiddenWidget();
        $this->defaults[self::$CSRFFieldName] =
            $manager->getToken($tokenId)->getValue();

        return $this;
    }

    public function getCSRFToken(bool|string|null $secret = null): string
    {
        return $this->csrfManager()->getToken(
            $this->csrfTokenId(),
        )->getValue();
    }

    public function isCSRFProtected(): bool
    {
        return isset($this->validatorSchema[self::$CSRFFieldName]);
    }

    public static function setCSRFFieldName(string $name): void
    {
        self::$CSRFFieldName = $name;
    }

    public static function getCSRFFieldName(): string
    {
        return self::$CSRFFieldName;
    }

    public function enableLocalCSRFProtection(
        bool|string|null $secret = null,
    ): void {
        $this->localCSRFSecret = $secret ?? true;
        $this->addCSRFProtection($this->localCSRFSecret);
    }

    public function disableLocalCSRFProtection(): void
    {
        $this->localCSRFSecret = false;
        unset(
            $this->validatorSchema[self::$CSRFFieldName],
            $this->widgetSchema[self::$CSRFFieldName],
            $this->defaults[self::$CSRFFieldName],
        );
    }

    public static function enableCSRFProtection(
        bool|string|null $secret = null,
    ): void {
        self::$CSRFSecret = $secret ?? true;
    }

    public static function disableCSRFProtection(): void
    {
        self::$CSRFSecret = false;
    }

    public function isMultipart(): bool
    {
        return $this->widgetSchema->needsMultipartForm();
    }

    public function renderFormTag(
        string $url,
        array $attributes = [],
    ): string {
        $method = strtolower((string) ($attributes['method'] ?? 'post'));
        $attributes['action'] = $url;
        $attributes['method'] = in_array($method, ['get', 'post'], true)
            ? $method
            : 'post';

        if ($this->isMultipart()) {
            $attributes['enctype'] = 'multipart/form-data';
        }

        $methodField = in_array($method, ['get', 'post'], true)
            ? ''
            : $this->widgetSchema->renderTag('input', [
                'type' => 'hidden',
                'name' => 'sf_method',
                'value' => $method,
                'id' => false,
            ]);

        return '<form'.$this->widgetSchema->attributesToHtml($attributes).'>'
            .$methodField;
    }

    public function useFields(
        array $fields = [],
        bool $ordered = true,
    ): void {
        $hidden = [];

        foreach ($this as $name => $field) {
            if ($field->isHidden()) {
                $hidden[] = $name;
            } elseif (!in_array($name, $fields, true)) {
                unset($this[$name]);
            }
        }

        if ($ordered) {
            $this->widgetSchema->setPositions([
                ...$fields,
                ...$hidden,
            ]);
        }
    }

    public function count(): int
    {
        return count($this->widgetSchema);
    }

    public function getIterator(): \Traversable
    {
        foreach ($this->widgetSchema->getPositions() as $name) {
            yield $name => $this->field($name);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->widgetSchema[$offset]);
    }

    public function offsetGet(mixed $offset): ?FormField
    {
        return $this->offsetExists($offset)
            ? $this->field((string) $offset)
            : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \LogicException('Cannot update bound form fields.');
    }

    public function offsetUnset(mixed $offset): void
    {
        unset(
            $this->widgetSchema[$offset],
            $this->validatorSchema[$offset],
            $this->defaults[$offset],
            $this->taintedValues[$offset],
            $this->values[$offset],
            $this->embeddedForms[$offset],
        );
    }

    private function field(string $name): FormField
    {
        $widget = $this->widgetSchema[$name]
            ?? throw new \InvalidArgumentException(sprintf(
                'Widget "%s" does not exist.',
                $name,
            ));
        $value = $this->isBound
            ? ($this->deepValue($this->taintedValues, $name)
                ?? $this->deepValue($this->taintedFiles, $name))
            : ($this->defaults[$name] ?? $widget->getDefault());

        return new FormField(
            $widget,
            $this->widgetSchema,
            $name,
            $value,
            $this->errorSchema[$name],
        );
    }

    private function installFormatter(): void
    {
        $class = Configuration::get('app_b5_theme', false)
            ? 'arB5WidgetFormSchemaFormatter'
            : 'sfDrupalWidgetFormSchemaFormatter';

        if (!class_exists($class)) {
            return;
        }

        $formatter = new $class($this->widgetSchema);
        $formatter->form = $this;
        $this->widgetSchema->addFormFormatter('atom', $formatter);
        $this->widgetSchema->setFormFormatterName('atom');
    }

    private function effectiveCsrfSecret(): bool|string|null
    {
        if (null !== $this->localCSRFSecret) {
            return $this->localCSRFSecret;
        }

        if (null !== self::$CSRFSecret) {
            return self::$CSRFSecret;
        }

        return Configuration::get('sf_csrf_secret', false);
    }

    private function csrfManager(): CsrfTokenManagerInterface
    {
        return $this->csrfManager ??= new CsrfTokenManager(
            storage: new ContextCsrfTokenStorage(),
            namespace: 'atom-',
        );
    }

    private function csrfTokenId(): string
    {
        return str_replace('\\', '.', static::class);
    }

    private function normalizeFiles(array $files): array
    {
        foreach ($files as $name => $file) {
            if ($file instanceof UploadedFile) {
                $files[$name] = [
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                    'tmp_name' => $file->getPathname(),
                    'error' => $file->getError(),
                    'size' => $file->getSize(),
                ];
            } elseif (is_array($file)) {
                $files[$name] = $this->normalizeFiles($file);
            }
        }

        return $files;
    }

    private function deepUnion(array $values, array $files): array
    {
        foreach ($files as $name => $file) {
            $values[$name] = is_array($file)
                && isset($values[$name])
                && is_array($values[$name])
                && !isset($file['tmp_name'])
                    ? $this->deepUnion($values[$name], $file)
                    : $file;
        }

        return $values;
    }

    private function deepValue(array $values, string $name): mixed
    {
        return $values[$name] ?? null;
    }
}
