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

final class TemplateRenderer
{
    private Context $context;

    public function render(
        string $templatePath,
        array $variables,
        Context $context,
    ): string {
        $this->context = $context;
        $bufferLevel = ob_get_level();
        $sf_context = $context;
        $sf_request = $context->getRequest();
        $sf_response = $context->getResponse();
        $sf_user = $context->getUser();
        $variables = [
            'sf_context' => $sf_context,
            'sf_request' => $sf_request,
            'sf_response' => $sf_response,
            'sf_user' => $sf_user,
        ] + $variables;

        OutputEscaper::markClassesAsSafe([
            'sfForm',
            'sfFormField',
            'sfFormFieldSchema',
            'sfModelGeneratorHelper',
        ]);
        $escapingMethod = $this->escapingMethod();
        $sf_data = OutputEscaper::escape(
            $escapingMethod,
            $variables,
        );

        if ((bool) Configuration::get('sf_escaping_strategy', true)) {
            $escaped = [];

            foreach ($sf_data as $name => $value) {
                $escaped[$name] = $value;
            }

            $variables = $escaped;
        }

        extract($variables, \EXTR_SKIP);
        ob_start();

        try {
            include $templatePath;

            return (string) ob_get_clean();
        } catch (\Throwable $exception) {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }

            throw $exception;
        }
    }

    private function escapingMethod(): string
    {
        if (!(bool) Configuration::get('sf_escaping_strategy', true)) {
            return 'esc_raw';
        }

        $method = (string) Configuration::get(
            'sf_escaping_method',
            'ESC_SPECIALCHARS',
        );

        if (defined($method)) {
            $method = (string) constant($method);
        }

        if (!is_callable($method)) {
            throw new BridgeException(sprintf(
                'The escaping method "%s" is not available.',
                $method,
            ));
        }

        return $method;
    }
}
