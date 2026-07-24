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

namespace Atom\Controller;

use Atom\Framework\Routing\RouteCompiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ErrorController;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final readonly class LegacyErrorController
{
    public function __construct(
        private LegacyController $legacyController,
        private ErrorController $errorController,
    ) {}

    public function __invoke(
        Request $request,
        \Throwable $exception,
    ): Response {
        if (
            !$exception instanceof HttpExceptionInterface
            || 404 !== $exception->getStatusCode()
        ) {
            return ($this->errorController)($exception);
        }

        $request->attributes->set('module', 'admin');
        $request->attributes->set('action', 'error404');
        $request->attributes->set('_route', 'atom_error_404');
        $request->attributes->remove('_atom_resource');
        $request->attributes->remove(RouteCompiler::CLASS_ATTRIBUTE);
        $response = ($this->legacyController)($request);
        $response->setStatusCode(404);

        return $response;
    }
}
