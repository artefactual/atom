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

class ValidatorError extends \Exception
{
    public function __construct(
        protected Validator $validator,
        protected string $errorCode,
        protected array $arguments = [],
    ) {
        parent::__construct(strtr(
            $this->getMessageFormat(),
            $this->getArguments(),
        ));
    }

    public function __toString(): string
    {
        return $this->getMessage();
    }

    public function getValue(): mixed
    {
        return $this->arguments['value'] ?? null;
    }

    public function getValidator(): Validator
    {
        return $this->validator;
    }

    public function getArguments(bool $raw = false): array
    {
        if ($raw) {
            return $this->arguments;
        }

        $arguments = [];

        foreach ($this->arguments as $name => $value) {
            if (is_scalar($value) || null === $value) {
                $arguments['%'.$name.'%'] = htmlspecialchars(
                    (string) $value,
                    \ENT_QUOTES | \ENT_SUBSTITUTE,
                    Validator::getCharset(),
                );
            }
        }

        return $arguments;
    }

    public function getMessageFormat(): string
    {
        return $this->validator->getMessage($this->errorCode)
            ?? $this->errorCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
