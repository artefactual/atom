<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * AtoM is free software: you can redistribute it and/or modify it under the
 * terms of the GNU Affero General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 */

declare(strict_types=1);

namespace Atom\Framework\Console;

use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\EventDispatcher;
use Atom\Framework\Bridge\RequestAdapter;
use Atom\Framework\Bridge\ResponseAdapter;
use Atom\Framework\Bridge\RuntimeConfiguration;
use Atom\Framework\Bridge\TranslatorFactory;
use Atom\Framework\Bridge\User;
use Atom\Framework\Bridge\ViewRuntimeFactory;
use Atom\Framework\Routing\ResourceRouteResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

final readonly class CliContextFactory
{
    public function __construct(
        private User $user,
        private RuntimeConfiguration $configuration,
        private EventDispatcher $eventDispatcher,
        private RouterInterface $router,
        private TranslatorFactory $translatorFactory,
        private ViewRuntimeFactory $viewRuntimeFactory,
        private ResourceRouteResolver $resourceRouteResolver,
    ) {}

    public function create(): Context
    {
        $request = Request::create('http://localhost/');
        $this->user->beginRequest($request);

        return new Context(
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
    }
}
