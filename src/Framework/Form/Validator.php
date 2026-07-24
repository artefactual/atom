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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validation;

class Validator extends OptionSet
{
    protected array $messages = [];

    public function __construct(
        array $options = [],
        array $messages = [],
    ) {
        $this->addOption('required', false);
        $this->addOption('trim', false);
        $this->addOption('empty_value');
        $this->addMessage('required', 'Required.');
        $this->addMessage('invalid', 'Invalid.');
        $this->configure($options, $messages);
        $this->applyOptions($options);

        foreach ($messages as $name => $message) {
            $this->setMessage((string) $name, (string) $message);
        }
    }

    public function clean(mixed $value): mixed
    {
        if (is_string($value) && $this->getOption('trim')) {
            $value = trim($value);
        }

        if ($this->isEmpty($value)) {
            if ($this->getOption('required')) {
                throw new ValidatorError($this, 'required', [
                    'value' => $value,
                ]);
            }

            return $this->getOption('empty_value');
        }

        try {
            return $this->doClean($value);
        } catch (ValidatorError $error) {
            throw $error;
        } catch (\Throwable) {
            throw new ValidatorError($this, 'invalid', ['value' => $value]);
        }
    }

    public function setMessage(string $name, string $message): static
    {
        $this->messages[$name] = $message;

        return $this;
    }

    public function addMessage(string $name, string $message): void
    {
        $this->messages[$name] ??= $message;
    }

    public function getMessage(string $name): ?string
    {
        return $this->messages[$name] ?? null;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    public static function getCharset(): string
    {
        return 'UTF-8';
    }

    protected function configure(
        array $options = [],
        array $messages = [],
    ) {}

    protected function doClean(mixed $value)
    {
        return $value;
    }

    protected function assert(
        mixed $value,
        array|Constraint $constraints,
        string $code = 'invalid',
    ): void {
        $violations = Validation::createValidator()->validate(
            $value,
            $constraints,
        );

        if (0 < count($violations)) {
            throw new ValidatorError($this, $code, ['value' => $value]);
        }
    }

    private function isEmpty(mixed $value): bool
    {
        return null === $value || '' === $value
            || (is_array($value) && [] === $value);
    }
}
