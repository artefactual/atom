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

use Atom\Framework\Bridge\Context;
use Symfony\Component\Security\Csrf\Exception\TokenNotFoundException;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;

final class ContextCsrfTokenStorage implements TokenStorageInterface
{
    private const ATTRIBUTE = 'form_csrf_tokens';
    private static array $fallbackTokens = [];

    public function getToken(string $tokenId): string
    {
        $tokens = $this->tokens();

        if (!isset($tokens[$tokenId])) {
            throw new TokenNotFoundException(sprintf(
                'The CSRF token "%s" does not exist.',
                $tokenId,
            ));
        }

        return $tokens[$tokenId];
    }

    public function setToken(
        string $tokenId,
        #[\SensitiveParameter] string $token,
    ): void {
        $tokens = $this->tokens();
        $tokens[$tokenId] = $token;
        $this->store($tokens);
    }

    public function removeToken(string $tokenId): ?string
    {
        $tokens = $this->tokens();
        $token = $tokens[$tokenId] ?? null;
        unset($tokens[$tokenId]);
        $this->store($tokens);

        return $token;
    }

    public function hasToken(string $tokenId): bool
    {
        return isset($this->tokens()[$tokenId]);
    }

    private function tokens(): array
    {
        if (!Context::hasInstance()) {
            return self::$fallbackTokens;
        }

        return (array) Context::getInstance()->getUser()->getAttribute(
            self::ATTRIBUTE,
            [],
        );
    }

    private function store(array $tokens): void
    {
        if (!Context::hasInstance()) {
            self::$fallbackTokens = $tokens;

            return;
        }

        Context::getInstance()->getUser()->setAttribute(
            self::ATTRIBUTE,
            $tokens,
        );
    }
}
