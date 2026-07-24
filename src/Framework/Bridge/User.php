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

namespace Atom\Framework\Bridge;

use Atom\Framework\Security\SecurityConfiguration;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

require_once __DIR__.'/ZendAclRoleInterface.php';

class User implements \ArrayAccess, \Zend_Acl_Role_Interface
{
    public const ATTRIBUTE_NAMESPACE = 'symfony/user/sfUser/attributes';
    public const CULTURE_NAMESPACE = 'symfony/user/sfUser/culture';
    public const LAST_REQUEST_NAMESPACE =
        'symfony/user/sfUser/lastRequest';
    public const AUTH_NAMESPACE = 'symfony/user/sfUser/authenticated';
    public const CREDENTIAL_NAMESPACE =
        'symfony/user/sfUser/credentials';
    public const FLASH_NAMESPACE = 'symfony/user/sfUser/flash';

    public mixed $user = null;

    private ParameterHolder $attributes;
    private ParameterHolder $flashes;
    private bool $authenticated = false;
    private string $culture = 'en';
    private array $credentials = [];
    private ?SessionInterface $session = null;
    private int $lastRequest = 0;
    private bool $timedOut = false;
    private bool $cultureChanged = false;

    public function __construct(
        private readonly ?SecurityConfiguration $security = null,
    ) {
        $this->reset();
    }

    public function __toString(): string
    {
        return is_object($this->user)
            ? (string) ($this->user->username ?? '')
            : 'NULL';
    }

    public function beginRequest(Request $request): void
    {
        $this->reset();

        if (!$request->hasSession()) {
            return;
        }

        $this->session = $request->getSession();
        $this->attributes->add(
            (array) $this->session->get(self::ATTRIBUTE_NAMESPACE, []),
        );
        $this->flashes->add(
            (array) $this->session->get(self::FLASH_NAMESPACE, []),
        );
        $this->authenticated = (bool) $this->session->get(
            self::AUTH_NAMESPACE,
            false,
        );
        $this->credentials = array_fill_keys(
            (array) $this->session->get(
                self::CREDENTIAL_NAMESPACE,
                [],
            ),
            true,
        );
        $this->culture = (string) $this->session->get(
            self::CULTURE_NAMESPACE,
            Configuration::get('sf_default_culture', 'en'),
        );
        $this->lastRequest = (int) $this->session->get(
            self::LAST_REQUEST_NAMESPACE,
            0,
        );
        $timeout = 1800;

        if (
            $this->authenticated
            && 0 < $this->lastRequest
            && time() - $this->lastRequest >= $timeout
        ) {
            $this->timedOut = true;
            $this->authenticated = false;
            $this->credentials = [];
        }

        if ($this->authenticated && null !== $this->getUserID()) {
            try {
                $this->user = \QubitUser::getById($this->getUserID());
            } catch (\Exception) {
                $this->user = null;
            }

            if (
                !is_object($this->user)
                || !$this->user->active
            ) {
                $this->signOut();
            }
        }

        $this->lastRequest = time();
    }

    public function persist(): void
    {
        if (null === $this->session) {
            return;
        }

        $this->session->set(
            self::ATTRIBUTE_NAMESPACE,
            $this->attributes->getAll(),
        );
        $this->session->set(
            self::FLASH_NAMESPACE,
            $this->flashes->getAll(),
        );
        $this->session->set(
            self::AUTH_NAMESPACE,
            $this->authenticated,
        );
        $this->session->set(
            self::CREDENTIAL_NAMESPACE,
            array_keys($this->credentials),
        );
        $this->session->set(self::CULTURE_NAMESPACE, $this->culture);
        $this->session->set(
            self::LAST_REQUEST_NAMESPACE,
            $this->lastRequest,
        );
    }

    public function cultureChanged(): bool
    {
        return $this->cultureChanged;
    }

    public function getRoleId(): mixed
    {
        if ($this->isAuthenticated()) {
            return $this->getUserID();
        }

        return class_exists('QubitAclGroup')
            ? \QubitAclGroup::ANONYMOUS_ID
            : 0;
    }

    public function getOptions(): array
    {
        return [
            'timeout' => 1800,
            'default_culture' => Configuration::get(
                'sf_default_culture',
                'en',
            ),
        ];
    }

    public function isTimedOut(): bool
    {
        return $this->timedOut;
    }

    public function getLastRequestTime(): int
    {
        return $this->lastRequest;
    }

    public function getAttribute(
        string $name,
        mixed $default = null,
        string $namespace = self::ATTRIBUTE_NAMESPACE,
    ): mixed {
        return $this->attributes->get($namespace.'/'.$name, $default);
    }

    public function setAttribute(
        string $name,
        mixed $value,
        string $namespace = self::ATTRIBUTE_NAMESPACE,
    ): void {
        $this->attributes->set($namespace.'/'.$name, $value);
    }

    public function hasAttribute(
        string $name,
        string $namespace = self::ATTRIBUTE_NAMESPACE,
    ): bool {
        return $this->attributes->has($namespace.'/'.$name);
    }

    public function removeAttribute(
        string $name,
        string $namespace = self::ATTRIBUTE_NAMESPACE,
    ): mixed {
        return $this->attributes->remove($namespace.'/'.$name);
    }

    public function getAttributeHolder(): ParameterHolder
    {
        return $this->attributes;
    }

    public function setFlash(
        string $name,
        mixed $value,
        bool $persist = true,
    ): void {
        $this->flashes->set($name, $value);
    }

    public function getFlash(string $name, mixed $default = null): mixed
    {
        return $this->flashes->remove($name, $default);
    }

    public function hasFlash(string $name): bool
    {
        return $this->flashes->has($name);
    }

    public function isAuthenticated(): bool
    {
        return !Configuration::get('app_read_only', false)
            && $this->authenticated;
    }

    public function setAuthenticated(bool $authenticated): void
    {
        if ($authenticated === $this->authenticated) {
            return;
        }

        $this->authenticated = $authenticated;

        if (!$authenticated) {
            $this->clearCredentials();
        }

        if (null !== $this->session) {
            $this->session->migrate(true);
        }
    }

    public function getCulture(): string
    {
        return $this->culture;
    }

    public function setCulture(string $culture): void
    {
        if ($culture !== $this->culture) {
            $this->culture = $culture;
            $this->cultureChanged = true;
            PropelBridge::setDefaultCulture($culture);
        }
    }

    public function addCredential(array|string $credentials): void
    {
        foreach ((array) $credentials as $credential) {
            $this->credentials[(string) $credential] = true;
        }
    }

    public function addCredentials(array|string ...$credentials): void
    {
        foreach ($credentials as $credential) {
            $this->addCredential($credential);
        }
    }

    public function hasCredential(
        array|string $credentials,
        bool $useAnd = true,
    ): bool {
        if (!is_array($credentials)) {
            return isset($this->credentials[$credentials]);
        }

        foreach ($credentials as $credential) {
            $matches = $this->hasCredential($credential, !$useAnd);

            if (($useAnd && !$matches) || (!$useAnd && $matches)) {
                return !$useAnd;
            }
        }

        return $useAnd;
    }

    public function getCredentials(): array
    {
        return array_keys($this->credentials);
    }

    public function clearCredentials(): void
    {
        $this->credentials = [];
    }

    public function removeCredential(string $credential): void
    {
        unset($this->credentials[$credential]);
    }

    public function signIn(object $user): void
    {
        $this->setAuthenticated(true);
        $this->user = $user;

        if (is_callable([$user, 'getAclGroups'])) {
            foreach ($user->getAclGroups() as $group) {
                $this->addCredential(
                    $group->getName(['culture' => 'en']),
                );
            }
        }

        $this->setAttribute('user_id', $user->id ?? null);
        $this->setAttribute('user_slug', $user->slug ?? null);
        $this->setAttribute('user_name', $user->username ?? null);
    }

    public function signOut(): void
    {
        $this->attributes->removeNamespace('credentialScope');
        $this->setAuthenticated(false);
        $this->user = null;

        foreach ([
            'user_id',
            'user_slug',
            'user_name',
            'login_route',
            'nav_context_module',
        ] as $attribute) {
            $this->removeAttribute($attribute);
        }
    }

    public function getUserID(): mixed
    {
        return $this->getAttribute('user_id');
    }

    public function getUserSlug(): mixed
    {
        return $this->getAttribute('user_slug');
    }

    public function getUserName(): mixed
    {
        return $this->getAttribute('user_name');
    }

    public function authenticate(string $username, string $password): bool
    {
        return $this->authenticateWithBasicAuth($username, $password);
    }

    public function authenticateWithBasicAuth(
        string $username,
        string $password,
    ): bool {
        if ('anonymous' === $username || !class_exists('QubitUser')) {
            return false;
        }

        $user = \QubitUser::checkCredentials(
            $username,
            $password,
            $error,
        );

        if (null === $user) {
            return false;
        }

        $this->signIn($user);

        return true;
    }

    public function getQubitUser(): mixed
    {
        return $this->user;
    }

    public function getAclGroups(): array
    {
        if ($this->isAuthenticated() && is_object($this->user)) {
            return $this->user->getAclGroups();
        }

        if (class_exists('QubitAclGroup')) {
            return [\QubitAclGroup::getById(
                \QubitAclGroup::ANONYMOUS_ID,
            )];
        }

        return [];
    }

    public function hasGroup(array|int|string $groups): bool
    {
        if ($this->isAuthenticated() && is_object($this->user)) {
            return $this->user->hasGroup($groups);
        }

        return class_exists('QubitAclGroup')
            && in_array(
                \QubitAclGroup::ANONYMOUS_ID,
                (array) $groups,
                true,
            );
    }

    public function listGroups(): array
    {
        if ($this->isAuthenticated() && is_object($this->user)) {
            $groups = [\QubitAclGroup::getById(
                \QubitAclGroup::AUTHENTICATED_ID,
            )];

            foreach ($this->user->getAclGroups() as $group) {
                $groups[] = $group;
            }

            return $groups;
        }

        return $this->getAclGroups();
    }

    public function checkModuleActionAccess(
        string $module,
        string $action,
    ): bool {
        if (null === $this->security) {
            return true;
        }

        $security = $this->security->forAction($module, $action);

        return !($security['is_secure'] ?? false)
            || (
                $this->isAuthenticated()
                && (
                    !isset($security['credentials'])
                    || $this->hasCredential($security['credentials'])
                )
            );
    }

    public function getModuleSecurityValue(
        string $action,
        string $setting,
        mixed $default = null,
        ?string $module = null,
    ): mixed {
        $module ??= Context::hasInstance()
            ? Context::getInstance()->getModuleName()
            : null;

        if (null === $this->security || null === $module) {
            return $default;
        }

        return $this->security
            ->forAction($module, $action)[strtolower($setting)]
            ?? $default;
    }

    public function isAdministrator(): bool
    {
        return class_exists('QubitAclGroup')
            ? $this->hasGroup(\QubitAclGroup::ADMINISTRATOR_ID)
            : $this->hasCredential('administrator');
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->hasAttribute((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute((string) $offset, false);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->removeAttribute((string) $offset);
    }

    private function reset(): void
    {
        $this->attributes = new ParameterHolder();
        $this->flashes = new ParameterHolder();
        $this->authenticated = false;
        $this->culture = (string) Configuration::get(
            'sf_default_culture',
            'en',
        );
        $this->credentials = [];
        $this->session = null;
        $this->lastRequest = 0;
        $this->timedOut = false;
        $this->cultureChanged = false;
        $this->user = null;
    }
}
