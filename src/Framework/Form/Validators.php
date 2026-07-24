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

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Languages;
use Symfony\Component\Validator\Constraints as Assert;

class StringValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ) {
        $this->addOption('min_length');
        $this->addOption('max_length');
        $this->addMessage(
            'min_length',
            '"%value%" is too short (%min_length% characters min).',
        );
        $this->addMessage(
            'max_length',
            '"%value%" is too long (%max_length% characters max).',
        );
    }

    protected function doClean(mixed $value)
    {
        if (!is_scalar($value) && !$value instanceof \Stringable) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        $value = (string) $value;
        $constraints = [];

        if (null !== $this->getOption('min_length')) {
            $constraints[] = new Assert\Length(
                min: (int) $this->getOption('min_length'),
            );
        }

        if (null !== $this->getOption('max_length')) {
            $constraints[] = new Assert\Length(
                max: (int) $this->getOption('max_length'),
            );
        }

        if ([] !== $constraints) {
            $this->assert($value, $constraints, $this->lengthCode($value));
        }

        return $value;
    }

    private function lengthCode(string $value): string
    {
        $length = mb_strlen($value);

        if (
            null !== $this->getOption('min_length')
            && $length < $this->getOption('min_length')
        ) {
            return 'min_length';
        }

        return 'max_length';
    }
}

class IntegerValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage('min', '"%value%" must be at least %min%.');
        $this->addMessage('max', '"%value%" must be at most %max%.');
    }

    protected function doClean(mixed $value): int
    {
        $clean = filter_var($value, \FILTER_VALIDATE_INT);

        if (false === $clean) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        if (
            null !== $this->getOption('min')
            && $clean < $this->getOption('min')
        ) {
            throw new ValidatorError($this, 'min', [
                'value' => $value,
                'min' => $this->getOption('min'),
            ]);
        }

        if (
            null !== $this->getOption('max')
            && $clean > $this->getOption('max')
        ) {
            throw new ValidatorError($this, 'max', [
                'value' => $value,
                'max' => $this->getOption('max'),
            ]);
        }

        return $clean;
    }
}

class NumberValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage('min', '"%value%" must be at least %min%.');
        $this->addMessage('max', '"%value%" must be at most %max%.');
    }

    protected function doClean(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        $clean = (float) $value;

        foreach (['min' => -1, 'max' => 1] as $option => $direction) {
            $limit = $this->getOption($option);

            if (
                null !== $limit
                && $direction * $clean > $direction * (float) $limit
            ) {
                throw new ValidatorError($this, $option, [
                    'value' => $value,
                    $option => $limit,
                ]);
            }
        }

        return $clean;
    }
}

class BooleanValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption(
            'true_values',
            ['true', 't', 'yes', 'y', 'on', '1', 1, true],
        );
        $this->addOption(
            'false_values',
            ['false', 'f', 'no', 'n', 'off', '0', 0, false],
        );
    }

    protected function doClean(mixed $value): bool
    {
        if (in_array($value, $this->getOption('true_values'), true)) {
            return true;
        }

        if (in_array($value, $this->getOption('false_values'), true)) {
            return false;
        }

        throw new ValidatorError($this, 'invalid', ['value' => $value]);
    }
}

class ChoiceValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addRequiredOption('choices');
        $this->addOption('multiple', false);
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage(
            'min',
            'At least %min% values must be selected.',
        );
        $this->addMessage(
            'max',
            'At most %max% values must be selected.',
        );
    }

    protected function doClean(mixed $value): mixed
    {
        $values = $this->getOption('multiple') ? $value : [$value];

        if (!is_array($values)) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        $choices = $this->choices();

        foreach ($values as $item) {
            if (!in_array($item, $choices, false)) {
                throw new ValidatorError(
                    $this,
                    'invalid',
                    ['value' => $item],
                );
            }
        }

        $count = count($values);

        foreach (['min' => -1, 'max' => 1] as $option => $direction) {
            $limit = $this->getOption($option);

            if (
                null !== $limit
                && $direction * $count > $direction * (int) $limit
            ) {
                throw new ValidatorError($this, $option, [
                    'value' => $value,
                    $option => $limit,
                    'count' => $count,
                ]);
            }
        }

        return $this->getOption('multiple') ? $values : $values[0];
    }

    protected function choices(): array
    {
        $choices = $this->getOption('choices');

        if (is_callable($choices)) {
            $choices = $choices();
        }

        return array_values((array) $choices);
    }
}

class EmailValidator extends StringValidator
{
    protected function doClean(mixed $value): string
    {
        $value = parent::doClean($value);
        $this->assert($value, new Assert\Email());

        return $value;
    }
}

class UrlValidator extends StringValidator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        parent::configure($options, $messages);
        $this->addOption('protocols', ['http', 'https', 'ftp', 'ftps']);
    }

    protected function doClean(mixed $value): string
    {
        $value = parent::doClean($value);
        $this->assert(
            $value,
            new Assert\Url(protocols: $this->getOption('protocols')),
        );

        return $value;
    }
}

class RegexValidator extends StringValidator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        parent::configure($options, $messages);
        $this->addRequiredOption('pattern');
        $this->addOption('must_match', true);
    }

    protected function doClean(mixed $value): string
    {
        $value = parent::doClean($value);
        $matches = 1 === preg_match($this->getOption('pattern'), $value);

        if ($matches !== (bool) $this->getOption('must_match')) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        return $value;
    }
}

class PassValidator extends Validator
{
    public function clean(mixed $value): mixed
    {
        return $value;
    }
}

class DateValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('date_format');
        $this->addOption('with_time', false);
        $this->addOption('date_output', 'Y-m-d');
        $this->addOption('datetime_output', 'Y-m-d H:i:s');
        $this->addOption('date_format_error');
        $this->addOption('min');
        $this->addOption('max');
        $this->addMessage('bad_format', 'Invalid date format.');
        $this->addMessage('min', 'The date must be after %min%.');
        $this->addMessage('max', 'The date must be before %max%.');
    }

    protected function doClean(mixed $value): string
    {
        if (is_array($value)) {
            $value = sprintf(
                '%04d-%02d-%02d %02d:%02d:%02d',
                $value['year'] ?? 0,
                $value['month'] ?? 0,
                $value['day'] ?? 0,
                $value['hour'] ?? 0,
                $value['minute'] ?? 0,
                $value['second'] ?? 0,
            );
        }

        if (
            is_string($value)
            && null !== $this->getOption('date_format')
            && 1 !== preg_match($this->getOption('date_format'), $value)
        ) {
            throw new ValidatorError(
                $this,
                'bad_format',
                ['value' => $value],
            );
        }

        try {
            $date = is_numeric($value)
                ? (new \DateTimeImmutable())->setTimestamp((int) $value)
                : new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        foreach (['min' => -1, 'max' => 1] as $option => $direction) {
            $limit = $this->getOption($option);

            if (
                null !== $limit
                && $direction * $date->getTimestamp()
                    > $direction * (new \DateTimeImmutable(
                        (string) $limit,
                    ))->getTimestamp()
            ) {
                throw new ValidatorError($this, $option, [
                    'value' => $value,
                    $option => $limit,
                ]);
            }
        }

        return $date->format($this->getOption('with_time')
            ? $this->getOption('datetime_output')
            : $this->getOption('date_output'));
    }
}

class CallbackValidator extends Validator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addRequiredOption('callback');
        $this->addOption('arguments', []);
    }

    protected function doClean(mixed $value): mixed
    {
        return ($this->getOption('callback'))(
            $this,
            $value,
            ...$this->getOption('arguments'),
        );
    }
}

class FileValidator extends Validator
{
    public function clean(mixed $value): mixed
    {
        if (
            is_array($value)
            && \UPLOAD_ERR_NO_FILE === ($value['error'] ?? null)
        ) {
            return parent::clean(null);
        }

        return parent::clean($value);
    }

    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('max_size');
        $this->addOption('mime_types');
        $this->addOption('validated_file_class', 'sfValidatedFile');
        $this->addOption('path');
        $this->addMessage('max_size', 'File is too large.');
        $this->addMessage('mime_types', 'Invalid mime type.');
        $this->addMessage('partial', 'The file was only partially uploaded.');
        $this->addMessage('no_tmp_dir', 'Missing a temporary folder.');
        $this->addMessage('cant_write', 'Failed to write file.');
        $this->addMessage('extension', 'File upload stopped by extension.');
    }

    protected function doClean(mixed $value): object
    {
        if (!is_array($value) || !isset($value['tmp_name'])) {
            throw new ValidatorError($this, 'invalid', ['value' => '']);
        }

        $error = (int) ($value['error'] ?? \UPLOAD_ERR_OK);
        $code = match ($error) {
            \UPLOAD_ERR_OK => null,
            \UPLOAD_ERR_INI_SIZE, \UPLOAD_ERR_FORM_SIZE => 'max_size',
            \UPLOAD_ERR_PARTIAL => 'partial',
            \UPLOAD_ERR_NO_TMP_DIR => 'no_tmp_dir',
            \UPLOAD_ERR_CANT_WRITE => 'cant_write',
            \UPLOAD_ERR_EXTENSION => 'extension',
            default => 'invalid',
        };

        if (null !== $code) {
            throw new ValidatorError($this, $code, ['value' => '']);
        }

        $uploaded = new UploadedFile(
            (string) $value['tmp_name'],
            (string) ($value['name'] ?? ''),
            (string) ($value['type'] ?? 'application/octet-stream'),
            $error,
            true,
        );
        $mimeTypes = $this->getOption('mime_types');

        if ('web_images' === $mimeTypes) {
            $mimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        }

        $constraint = new Assert\File(
            maxSize: $this->getOption('max_size'),
            mimeTypes: null === $mimeTypes ? [] : (array) $mimeTypes,
        );
        $this->assert($uploaded, $constraint, $this->fileErrorCode($uploaded));
        $class = $this->getOption('validated_file_class');

        return new $class(
            $uploaded->getClientOriginalName(),
            $uploaded->getMimeType()
                ?? $uploaded->getClientMimeType()
                ?? 'application/octet-stream',
            $uploaded->getPathname(),
            $uploaded->getSize(),
            $this->getOption('path'),
        );
    }

    private function fileErrorCode(UploadedFile $file): string
    {
        if (
            null !== $this->getOption('max_size')
            && $file->getSize() > $this->getOption('max_size')
        ) {
            return 'max_size';
        }

        return 'mime_types';
    }
}

class I18nChoiceCountryValidator extends ChoiceValidator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $options['choices'] ??= array_keys(Countries::getNames());
        parent::configure($options, $messages);
        $this->setOption('choices', $options['choices']);
    }
}

class I18nChoiceLanguageValidator extends ChoiceValidator
{
    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $options['choices'] ??= array_keys(Languages::getNames());
        parent::configure($options, $messages);
        $this->setOption('choices', $options['choices']);
    }
}

class AndValidator extends Validator
{
    private array $validators;

    public function __construct(
        array|Validator|null $validators = null,
        array $options = [],
        array $messages = [],
    ) {
        $this->validators = null === $validators
            ? []
            : (is_array($validators) ? $validators : [$validators]);
        parent::__construct($options, $messages);
    }

    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('halt_on_error', false);
    }

    protected function doClean(mixed $value): mixed
    {
        $errors = new ValidatorErrorSchema($this);

        foreach ($this->validators as $validator) {
            try {
                $value = $validator->clean($value);
            } catch (ValidatorError $error) {
                $errors->addError($error);

                if ($this->getOption('halt_on_error')) {
                    break;
                }
            }
        }

        if (0 < count($errors)) {
            throw $errors;
        }

        return $value;
    }
}

class OrValidator extends Validator
{
    private array $validators;

    public function __construct(
        array|Validator|null $validators = null,
        array $options = [],
        array $messages = [],
    ) {
        $this->validators = null === $validators
            ? []
            : (is_array($validators) ? $validators : [$validators]);
        parent::__construct($options, $messages);
    }

    protected function doClean(mixed $value): mixed
    {
        foreach ($this->validators as $validator) {
            try {
                return $validator->clean($value);
            } catch (ValidatorError) {
            }
        }

        throw new ValidatorError($this, 'invalid', ['value' => $value]);
    }
}

class SchemaValidator extends Validator implements \ArrayAccess
{
    private array $fields = [];
    private ?Validator $preValidator = null;
    private ?Validator $postValidator = null;

    public function __construct(
        ?array $fields = null,
        array $options = [],
        array $messages = [],
    ) {
        parent::__construct($options, $messages);

        foreach ($fields ?? [] as $name => $validator) {
            $this[$name] = $validator;
        }
    }

    public function __isset(string $name): bool
    {
        return $this->offsetExists($name);
    }

    public function __get(string $name): ?Validator
    {
        return $this->offsetGet($name);
    }

    public function __set(string $name, Validator $validator): void
    {
        $this->offsetSet($name, $validator);
    }

    public function __clone()
    {
        foreach ($this->fields as $name => $validator) {
            $this->fields[$name] = clone $validator;
        }

        $this->preValidator = null === $this->preValidator
            ? null
            : clone $this->preValidator;
        $this->postValidator = null === $this->postValidator
            ? null
            : clone $this->postValidator;
    }

    public function clean(mixed $value): array
    {
        if (null === $value) {
            $value = [];
        }

        if (!is_array($value)) {
            throw new \InvalidArgumentException(
                'A validator schema requires an array.',
            );
        }

        $errors = new ValidatorErrorSchema($this);

        try {
            $this->preValidator?->clean($value);
        } catch (ValidatorError $error) {
            $errors->addError($error);
        }

        $clean = [];

        foreach ($this->fields as $name => $validator) {
            try {
                $clean[$name] = $validator->clean($value[$name] ?? null);
            } catch (ValidatorError $error) {
                $clean[$name] = null;
                $errors->addError($error, (string) $name);
            }
        }

        foreach (array_diff_key($value, $this->fields) as $name => $extra) {
            if (!$this->getOption('allow_extra_fields')) {
                $errors->addError(new ValidatorError(
                    $this,
                    'extra_fields',
                    ['field' => $name],
                ));
            } elseif (!$this->getOption('filter_extra_fields')) {
                $clean[$name] = $extra;
            }
        }

        try {
            if (null !== $this->postValidator) {
                $clean = $this->postValidator->clean($clean);
            }
        } catch (ValidatorError $error) {
            $errors->addError($error);
        }

        if (0 < count($errors)) {
            throw $errors;
        }

        return $clean;
    }

    public function setPreValidator(Validator $validator): static
    {
        $this->preValidator = clone $validator;

        return $this;
    }

    public function getPreValidator(): ?Validator
    {
        return $this->preValidator;
    }

    public function setPostValidator(Validator $validator): static
    {
        $this->postValidator = clone $validator;

        return $this;
    }

    public function getPostValidator(): ?Validator
    {
        return $this->postValidator;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->fields[$offset]);
    }

    public function offsetGet(mixed $offset): ?Validator
    {
        return $this->fields[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof Validator) {
            throw new \InvalidArgumentException(
                'A schema field must be a Validator.',
            );
        }

        $this->fields[(string) $offset] = clone $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->fields[$offset]);
    }

    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('allow_extra_fields', false);
        $this->addOption('filter_extra_fields', true);
        $this->addMessage(
            'extra_fields',
            'Unexpected extra form field named "%field%".',
        );
    }
}

class SchemaCompareValidator extends Validator
{
    public const EQUAL = '==';
    public const NOT_EQUAL = '!=';
    public const IDENTICAL = '===';
    public const NOT_IDENTICAL = '!==';
    public const LESS_THAN = '<';
    public const LESS_THAN_EQUAL = '<=';
    public const GREATER_THAN = '>';
    public const GREATER_THAN_EQUAL = '>=';

    public function __construct(
        string $leftField,
        string $operator,
        string $rightField,
        array $options = [],
        array $messages = [],
    ) {
        $options += [
            'left_field' => $leftField,
            'operator' => $operator,
            'right_field' => $rightField,
        ];
        parent::__construct($options, $messages);
    }

    protected function configure(
        array $options = [],
        array $messages = [],
    ): void {
        $this->addOption('left_field');
        $this->addOption('operator');
        $this->addOption('right_field');
        $this->addOption('throw_global_error', false);
    }

    protected function doClean(mixed $value): array
    {
        if (!is_array($value)) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }

        $left = $value[$this->getOption('left_field')] ?? null;
        $right = $value[$this->getOption('right_field')] ?? null;
        $valid = match ($this->getOption('operator')) {
            self::EQUAL => $left == $right,
            self::NOT_EQUAL => $left != $right,
            self::IDENTICAL => $left === $right,
            self::NOT_IDENTICAL => $left !== $right,
            self::LESS_THAN => $left < $right,
            self::LESS_THAN_EQUAL => $left <= $right,
            self::GREATER_THAN => $left > $right,
            self::GREATER_THAN_EQUAL => $left >= $right,
            default => throw new \InvalidArgumentException(
                'Unknown schema comparison operator.',
            ),
        };

        if (!$valid) {
            $error = new ValidatorError($this, 'invalid', ['value' => $right]);

            if ($this->getOption('throw_global_error')) {
                throw $error;
            }

            $schema = new ValidatorErrorSchema($this);
            $schema->addError($error, $this->getOption('right_field'));

            throw $schema;
        }

        return $value;
    }
}
