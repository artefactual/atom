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

use Atom\Framework\Database\DatabaseManager;
use Atom\Framework\Database\DatabaseManagerProxy;
use Atom\Framework\Routing\ResourceRouteResolver;
use Symfony\Component\Routing\RouterInterface;

final class Context
{
    public readonly RoutingAdapter $routing;
    public readonly Translator $i18n;
    public readonly ControllerProxy $controller;
    public readonly DatabaseManager $databaseManager;
    private readonly ViewRuntime $viewRuntime;
    private array $objects;
    private static ?self $instance = null;

    public function __construct(
        public readonly RequestAdapter $request,
        public readonly ResponseAdapter $response,
        public readonly User $user,
        private readonly RuntimeConfiguration $configuration,
        private readonly EventDispatcher $eventDispatcher,
        RouterInterface $router,
        TranslatorFactory $translatorFactory,
        ViewRuntimeFactory $viewRuntimeFactory,
        private readonly LoggerAdapter $logger = new LoggerAdapter(),
        ?ResourceRouteResolver $resourceRouteResolver = null,
    ) {
        $this->routing = new RoutingAdapter(
            $router,
            $request,
            $resourceRouteResolver,
        );
        $this->i18n = $translatorFactory->create($user->getCulture());
        $this->controller = new ControllerProxy($this);
        $this->databaseManager = new DatabaseManagerProxy();
        $this->viewRuntime = $viewRuntimeFactory->create($this);
        $this->objects = [
            'request' => $this->request,
            'response' => $this->response,
            'user' => $this->user,
            'routing' => $this->routing,
            'i18n' => $this->i18n,
            'controller' => $this->controller,
            'databaseManager' => $this->databaseManager,
            'logger' => $this->logger,
        ];
    }

    public static function createInstance(mixed $configuration = null): self
    {
        new DatabaseManager();

        return self::getInstance();
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

    public function getI18N(): Translator
    {
        return $this->i18n;
    }

    public function getEventDispatcher(): EventDispatcher
    {
        return $this->eventDispatcher;
    }

    public function getLogger(): LoggerAdapter
    {
        return $this->logger;
    }

    public function getViewRuntime(): ViewRuntime
    {
        return $this->viewRuntime;
    }

    public function get(string $name): mixed
    {
        if (!$this->has($name)) {
            throw new BridgeException(sprintf(
                'The "%s" object does not exist in the current context.',
                $name,
            ));
        }

        return $this->objects[$name];
    }

    public function set(string $name, mixed $object): void
    {
        $this->objects[$name] = $object;
    }

    public function has(string $name): bool
    {
        return isset($this->objects[$name]);
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
                return $this->routing->generateInternalUri($target);
            }
        }

        $parameters = (array) $target;

        return $this->routing->generate(null, $parameters);
    }
}
