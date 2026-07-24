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

namespace Atom\Framework\Filter;

use Atom\Framework\Bridge\Configuration;
use Atom\Framework\Bridge\Context;
use Atom\Framework\Bridge\StopException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final readonly class RestApiFilter implements FilterHandler
{
    public function process(Context $context, callable $next): Response
    {
        Configuration::set('sf_web_debug', false);

        try {
            return $next();
        } catch (StopException $exception) {
            return $exception->response
                ?? $context->getResponse()->getSymfonyResponse();
        } catch (\Throwable $exception) {
            if (is_a($exception, 'QubitApiException')) {
                return new JsonResponse(
                    [
                        'id' => $exception->getId(),
                        'message' => $exception->getMessage(),
                    ],
                    $exception->getStatusCode(),
                );
            }

            return new JsonResponse([
                'id' => 'unknown',
                'message' => 'Unknown API error.',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
