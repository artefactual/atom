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

namespace Atom\Controller;

use Atom\Framework\Bridge\ActionRunner;
use Atom\Framework\Bridge\ArrayStorage;
use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\NotFoundException;
use Atom\Framework\Bridge\RequestAdapter;
use Atom\Framework\Bridge\ResponseAdapter;
use Atom\Framework\Bridge\RouteState;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Bridge\StopException;
use Atom\Framework\Bridge\TranslatorFactory;
use Atom\Framework\Bridge\User;
use Atom\Framework\Bridge\ViewRuntimeFactory;
use Atom\Framework\Filter\FilterPipeline;
use Atom\Framework\Routing\ResourceRouteResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\RouterInterface;

final readonly class LegacyController
{
    public function __construct(
        private ActionRunner $runner,
        private RouterInterface $router,
        private User $user,
        private RuntimeConfiguration $configuration,
        private EventDispatcher $eventDispatcher,
        private FilterPipeline $filters,
        private TranslatorFactory $translatorFactory,
        private ViewRuntimeFactory $viewRuntimeFactory,
        private ResourceRouteResolver $resourceRouteResolver,
    ) {}

    public function __invoke(Request $request): Response
    {
        $request->attributes->set(
            'sf_route',
            $request->attributes->get('sf_route', new RouteState()),
        );
        $request->attributes->get('sf_route')->resource = $request
            ->attributes
            ->get('_atom_resource');
        $context = new Context(
            new RequestAdapter($request),
            new ResponseAdapter(new Response()),
            $this->user,
            $this->configuration,
            $this->eventDispatcher,
            $this->router,
            $this->translatorFactory,
            $this->viewRuntimeFactory,
            resourceRouteResolver: $this->resourceRouteResolver,
        );
        Context::setInstance($context);

        try {
            $this->user->initialize(
                $this->eventDispatcher,
                new ArrayStorage(['auto_shutdown' => false]),
                ['timeout' => 1800],
            );

            return $this->filters->run(
                $context,
                fn (): Response => $this->runner->run($context),
            );
        } catch (StopException $exception) {
            return $exception->response
                ?? $context->getResponse()->getSymfonyResponse();
        } catch (NotFoundException $exception) {
            throw new NotFoundHttpException(
                $exception->getMessage(),
                $exception,
            );
        } finally {
            Context::setInstance(null);
        }
    }
}
