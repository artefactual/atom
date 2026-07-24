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

namespace Atom\Framework\Security;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\ForwardException;
use Atom\Framework\Bridge\User;

final readonly class SecurityEnforcer
{
    public function __construct(
        private SecurityConfiguration $configuration,
    ) {}

    public function enforce(
        User $user,
        string $module,
        string $action,
    ): array {
        $security = $this->configuration->forAction($module, $action);

        if (!($security['is_secure'] ?? false)) {
            return $security;
        }

        if (!$user->isAuthenticated()) {
            throw new ForwardException(
                (string) Configuration::get('sf_login_module', 'user'),
                (string) Configuration::get('sf_login_action', 'login'),
            );
        }

        $credentials = $security['credentials'] ?? null;

        if (
            null !== $credentials
            && !$user->hasCredential($credentials)
        ) {
            throw new ForwardException(
                (string) Configuration::get('sf_secure_module', 'admin'),
                (string) Configuration::get('sf_secure_action', 'secure'),
            );
        }

        return $security;
    }
}
