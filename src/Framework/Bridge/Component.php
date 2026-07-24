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

abstract class Component implements \ArrayAccess
{
    protected string $moduleName = '';
    protected string $actionName = '';
    protected Context $context;
    protected EventDispatcher $dispatcher;
    protected RequestAdapter $request;
    protected ResponseAdapter $response;
    protected ParameterHolder $varHolder;
    protected ParameterHolder $requestParameterHolder;

    public function __construct(
        Context $context,
        string $moduleName,
        string $actionName,
    ) {
        $this->initialize($context, $moduleName, $actionName);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->varHolder->setByRef($name, $value);
    }

    public function __isset(string $name): bool
    {
        return $this->varHolder->has($name);
    }

    public function __unset(string $name): void
    {
        $this->varHolder->remove($name);
    }

    public function __call(string $name, array $arguments): mixed
    {
        $event = $this->dispatcher->notifyUntil(new Event(
            $this,
            sprintf('component.method_not_found.%s', $name),
            ['arguments' => $arguments],
        ));

        if ($event->isProcessed()) {
            return $event->getReturnValue();
        }

        throw new BridgeException(sprintf(
            'Call to undefined method %s::%s().',
            static::class,
            $name,
        ));
    }

    public function initialize(
        Context $context,
        string $moduleName,
        string $actionName,
    ): void {
        $this->moduleName = $moduleName;
        $this->actionName = $actionName;
        $this->context = $context;
        $this->dispatcher = $context->getEventDispatcher();
        $this->varHolder = new ParameterHolder();
        $this->request = $context->getRequest();
        $this->response = $context->getResponse();
        $this->requestParameterHolder = $this->request->getParameterHolder();
    }

    abstract public function execute($request);

    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    public function getActionName(): string
    {
        return $this->actionName;
    }

    final public function getContext(): Context
    {
        return $this->context;
    }

    final public function getLogger(): LoggerAdapter
    {
        return $this->context->getLogger();
    }

    public function logMessage(
        mixed $message,
        string $priority = 'info',
    ): void {
        if (!Configuration::get('sf_logging_enabled', false)) {
            return;
        }

        if (!is_callable([$this->getLogger(), $priority])) {
            $priority = 'info';
        }

        $this->getLogger()->{$priority}((string) $message);
    }

    public function getRequestParameter(
        int|string $name,
        mixed $default = null,
    ): mixed {
        return $this->requestParameterHolder->get($name, $default);
    }

    public function hasRequestParameter(int|string $name): bool
    {
        return $this->requestParameterHolder->has($name);
    }

    public function getRequest(): RequestAdapter
    {
        return $this->request;
    }

    public function getResponse(): ResponseAdapter
    {
        return $this->response;
    }

    public function getController(): ControllerProxy
    {
        return $this->context->getController();
    }

    public function generateUrl(
        string $route,
        array $parameters = [],
        bool $absolute = false,
    ): string {
        return $this->context
            ->getRouting()
            ->generate($route, $parameters, $absolute);
    }

    public function getUser(): User
    {
        return $this->context->getUser();
    }

    public function setVar(
        int|string $name,
        mixed $value,
        bool $safe = false,
    ): void {
        $this->varHolder->set(
            $name,
            $safe ? new SafeValue($value) : $value,
        );
    }

    public function getVar(int|string $name): mixed
    {
        return $this->varHolder->get($name);
    }

    public function getVarHolder(): ParameterHolder
    {
        return $this->varHolder;
    }

    public function &__get(string $name): mixed
    {
        return $this->varHolder->get($name);
    }

    public function offsetExists(mixed $offset): bool
    {
        return $this->__isset((string) $offset);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->__get((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->__set((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        $this->__unset((string) $offset);
    }
}
