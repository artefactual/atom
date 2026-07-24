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

namespace Atom\EventSubscriber;

use Atom\Framework\Bridge\NotFoundException;
use Atom\Framework\Routing\ResourceRouteResolver;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

final readonly class ResourceRouteSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ResourceRouteResolver $resolver,
        private RouterInterface $router,
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['resolve', 24],
        ];
    }

    public function resolve(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $parameters = $request->attributes->all();

        try {
            while (null === $resolved = $this->resolver->resolve($parameters)) {
                $parameters = $this->nextMatch(
                    (string) ($parameters['_route'] ?? ''),
                    $request->getPathInfo(),
                );
            }
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException(
                $exception->getMessage(),
                $exception,
            );
        } catch (ResourceNotFoundException $exception) {
            throw new NotFoundHttpException(
                sprintf(
                    'No route found for "%s %s".',
                    $request->getMethod(),
                    $request->getPathInfo(),
                ),
                $exception,
            );
        }

        $request->attributes->add($resolved->parameters);
        $routeParameters = $resolved->parameters;
        unset($routeParameters['_route'], $routeParameters['_controller']);
        $request->attributes->set('_route_params', $routeParameters);

        if (null !== $resolved->resource) {
            $request->attributes->set(
                '_atom_resource',
                $resolved->resource,
            );
        }
    }

    private function nextMatch(string $routeName, string $path): array
    {
        $followingRoutes = new RouteCollection();
        $found = false;

        foreach ($this->router->getRouteCollection() as $name => $route) {
            if ($found) {
                $followingRoutes->add($name, $route);
            } elseif ($name === $routeName) {
                $found = true;
            }
        }

        if (!$found) {
            throw new ResourceNotFoundException(sprintf(
                'Matched route "%s" is not in the route collection.',
                $routeName,
            ));
        }

        return (new UrlMatcher(
            $followingRoutes,
            $this->router->getContext(),
        ))->match($path);
    }
}
