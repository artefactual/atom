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
use Symfony\Component\HttpFoundation\Response;

final readonly class MetaFilter implements FilterHandler
{
    public function process(Context $context, callable $next): Response
    {
        $context->getResponse()->addMeta(
            'title',
            (string) Configuration::get('app_siteTitle', 'AtoM'),
        );
        $context->getResponse()->addMeta(
            'description',
            (string) Configuration::get('app_siteDescription', ''),
        );

        try {
            return $next();
        } catch (\Throwable $exception) {
            if (!is_a(
                $exception,
                'Elastica\Exception\ExceptionInterface',
            )) {
                throw $exception;
            }

            $context->getRequest()->setParameter('exception', $exception);
            $context->getRequest()->setParameter('module', 'search');
            $context->getRequest()->setParameter('action', 'error');

            return $next();
        }
    }
}
