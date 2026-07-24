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

use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class CsrfTokenValidator extends Validator
{
    public function __construct(
        private readonly CsrfTokenManagerInterface $manager,
        private readonly string $tokenId,
    ) {
        parent::__construct(['required' => true], [
            'invalid' => 'CSRF attack detected.',
            'required' => 'CSRF token is missing.',
        ]);
    }

    protected function doClean(mixed $value): string
    {
        $value = (string) $value;

        if (!$this->manager->isTokenValid(
            new CsrfToken($this->tokenId, $value),
        )) {
            throw new ValidatorError($this, 'invalid', ['value' => '']);
        }

        return $value;
    }
}
