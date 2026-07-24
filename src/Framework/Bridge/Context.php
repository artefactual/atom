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

use Symfony\Component\Routing\RouterInterface;

final class Context
{
    public readonly RoutingAdapter $routing;
    public readonly Translator $i18n;
    public readonly ControllerProxy $controller;
    private static ?self $instance = null;

    public function __construct(
        public readonly RequestAdapter $request,
        public readonly ResponseAdapter $response,
        public readonly User $user,
        private readonly RuntimeConfiguration $configuration,
        private readonly EventDispatcher $eventDispatcher,
        RouterInterface $router,
        private readonly LoggerAdapter $logger = new LoggerAdapter(),
    ) {
        $this->routing = new RoutingAdapter($router, $request);
        $this->i18n = new Translator();
        $this->controller = new ControllerProxy($this);
    }

    public static function getInstance(): self
    {
        return self::$instance ?? throw new BridgeException(
            'No AtoM request context is active.',
        );
    }

    public static function hasInstance(): bool
    {
        return null !== self::$instance;
    }

    public static function setInstance(?self $context): void
    {
        self::$instance = $context;
    }

    public function getRequest(): RequestAdapter
    {
        return $this->request;
    }

    public function getResponse(): ResponseAdapter
    {
        return $this->response;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getRouting(): RoutingAdapter
    {
        return $this->routing;
    }

    public function getController(): ControllerProxy
    {
        return $this->controller;
    }

    public function getConfiguration(): RuntimeConfiguration
    {
        return $this->configuration;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getLogger(): LoggerAdapter
    {
        return $this->logger;
    }

    public function getModuleName(): ?string
    {
        return $this->request->getParameter('module');
    }

    public function getActionName(): ?string
    {
        return $this->request->getParameter('action');
    }

    public function urlFor(array|string $target): string
    {
        if (is_string($target)) {
            if (
                str_starts_with($target, '/')
                || preg_match('#^https?://#i', $target)
            ) {
                return $target;
            }

            if (str_starts_with($target, '@')) {
                return $this->routing->generate(substr($target, 1));
            }

            if (str_contains($target, '/')) {
                [$module, $action] = explode('/', $target, 2);

                return $this->routing->generate('default', [
                    'module' => $module,
                    'action' => $action,
                ]);
            }
        }

        $parameters = (array) $target;
        unset($parameters[0]);

        return $this->routing->generate(null, $parameters);
    }
}
