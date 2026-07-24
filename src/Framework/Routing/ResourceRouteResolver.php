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

namespace Atom\Framework\Routing;

use Atom\Framework\Bridge\NotFoundException;
use Atom\Framework\Configuration\ConfigurationException;

final readonly class ResourceRouteResolver
{
    private const METADATA_PLUGINS = [
        'isaar' => 'sfIsaarPlugin',
        'eac' => 'sfEacPlugin',
        'ead' => 'sfEadPlugin',
        'isad' => 'sfIsadPlugin',
        'dc' => 'sfDcPlugin',
        'skos' => 'sfSkosPlugin',
        'rad' => 'sfRadPlugin',
        'mods' => 'sfModsPlugin',
        'dacs' => 'arDacsPlugin',
        'isdf' => 'sfIsdfPlugin',
    ];

    private const DEFAULT_MODULES = [
        'informationobject' => false,
        'term' => 'term',
        'actor' => 'sfIsaarPlugin',
        'repository' => 'sfIsdiahPlugin',
        'function' => 'sfIsdfPlugin',
    ];

    public function __construct(
        private ResourceRepository $resources,
        private ResourceClassifier $classifier,
    ) {}

    public function resolve(array $parameters): ?ResolvedRoute
    {
        return match ($parameters[RouteCompiler::CLASS_ATTRIBUTE] ?? null) {
            'QubitResourceRoute' => $this->resolveResource($parameters),
            'QubitMetadataRoute' => $this->resolveMetadata($parameters),
            default => new ResolvedRoute($parameters),
        };
    }

    private function resolveResource(array $parameters): ResolvedRoute
    {
        $resource = $this->resources->findBySlug(
            (string) ($parameters['slug'] ?? ''),
        );

        if (
            null === $resource
            && !($parameters['throw404'] ?? false)
        ) {
            throw new NotFoundException(sprintf(
                'No resource exists for slug "%s".',
                $parameters['slug'] ?? '',
            ));
        }

        return new ResolvedRoute($parameters, $resource);
    }

    private function resolveMetadata(array $parameters): ?ResolvedRoute
    {
        if (in_array($parameters['action'] ?? null, ['add', 'copy'], true)) {
            $parameters['action'] = 'edit';
        }

        if (isset($parameters['slug'])) {
            $resource = $this->resources->findBySlug(
                (string) $parameters['slug'],
            );

            if (null === $resource) {
                return null;
            }

            $module = $this->moduleForResource($resource, $parameters);

            if (null === $module) {
                return null;
            }

            $parameters['module'] = $module;

            return new ResolvedRoute($parameters, $resource);
        }

        $module = $parameters['module'] ?? null;

        if (!is_string($module)) {
            return new ResolvedRoute($parameters);
        }

        if ('informationobject' === $module) {
            $template = $this->resources->defaultTemplate($module);

            if (false !== $template) {
                $parameters['module'] = $this->metadataPlugin($template);
            }
        } elseif (array_key_exists($module, self::DEFAULT_MODULES)) {
            $parameters['module'] = self::DEFAULT_MODULES[$module];
        }

        return new ResolvedRoute($parameters);
    }

    private function moduleForResource(
        object $resource,
        array $parameters,
    ): ?string {
        return match ($this->classifier->classify($resource)) {
            'repository' => 'sfIsdiahPlugin',
            'relation' => 'relation',
            'donor' => 'donor',
            'rights' => 'right',
            'rights_holder' => 'rightsholder',
            'user' => 'user',
            'actor' => $this->actionModule(
                ['isaar', 'eac'],
                $this->resources->defaultTemplate('actor'),
                $parameters,
            ),
            'function' => 'sfIsdfPlugin',
            'digital_object' => 'digitalobject',
            'information_object' => $this->informationObjectModule(
                $resource,
                $parameters,
            ),
            'accession' => 'accession',
            'deaccession' => 'deaccession',
            'term' => isset($parameters['template'])
                && 'skos' === $parameters['template']
                    ? 'sfSkosPlugin'
                    : 'term',
            'taxonomy' => 'taxonomy',
            'static_page' => 'staticpage',
            'physical_object' => 'physicalobject',
            'event' => 'event',
            default => null,
        };
    }

    private function informationObjectModule(
        object $resource,
        array $parameters,
    ): string {
        $default = $this->resources->defaultTemplate('informationobject');
        $assigned = $this->resources->informationObjectTemplate($resource);

        if (false !== $assigned) {
            $default = $assigned;
        }

        return $this->actionModule(
            ['isad', 'dc', 'mods', 'rad', 'ead', 'dacs'],
            $default,
            $parameters,
        );
    }

    private function actionModule(
        array $allowedTemplates,
        false|string $default,
        array $parameters,
    ): string {
        $template = $parameters['template'] ?? $default;

        if (
            !is_string($template)
            || !in_array($template, $allowedTemplates, true)
        ) {
            throw new ConfigurationException(sprintf(
                'Metadata code "%s" is not valid.',
                is_scalar($template) ? (string) $template : gettype($template),
            ));
        }

        return $this->metadataPlugin($template);
    }

    private function metadataPlugin(string $template): string
    {
        if (!isset(self::METADATA_PLUGINS[$template])) {
            throw new ConfigurationException(sprintf(
                'Metadata code "%s" is not valid.',
                $template,
            ));
        }

        return self::METADATA_PLUGINS[$template];
    }
}
