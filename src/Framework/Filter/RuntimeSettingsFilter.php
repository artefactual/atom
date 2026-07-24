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

final readonly class RuntimeSettingsFilter implements FilterHandler
{
    public function __construct(
        private SettingsRepository $settings,
        private string $environment,
    ) {}

    public function process(Context $context, callable $next): Response
    {
        try {
            $settings = $this->settings->all(
                $context->getUser()->getCulture(),
            );
        } catch (\PropelException $exception) {
            if ('test' !== $this->environment) {
                throw $exception;
            }

            $settings = [];
        }

        if (false !== $readOnly = getenv('ATOM_READ_ONLY')) {
            $settings['app_read_only'] = filter_var(
                $readOnly,
                \FILTER_VALIDATE_BOOLEAN,
            );
        }

        Configuration::add($settings);

        return $next();
    }
}
