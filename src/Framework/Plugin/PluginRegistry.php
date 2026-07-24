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

namespace Atom\Framework\Plugin;

final readonly class PluginRegistry
{
    private const CORE_PLUGINS = [
        'qbAclPlugin',
        'qtAccessionPlugin',
        'sfDrupalPlugin',
        'sfHistoryPlugin',
        'arElasticSearchPlugin',
        'sfPropelPlugin',
        'sfThumbnailPlugin',
        'sfTranslatePlugin',
        'sfWebBrowserPlugin',
        'sfPluginAdminPlugin',
    ];

    public function __construct(
        private string $projectDirectory,
        private ?bool $activateOidc = null,
    ) {}

    public function enabled(): array
    {
        $plugins = self::CORE_PLUGINS;

        if ($this->oidcIsActive()) {
            $plugins[] = 'arOidcPlugin';
        }

        return $plugins;
    }

    private function oidcIsActive(): bool
    {
        if (null !== $this->activateOidc) {
            return $this->activateOidc;
        }

        $marker = $this->projectDirectory.'/activate-oidc-plugin';
        $markerExists = is_file($marker) && 0 === filesize($marker);
        $environmentEnabled = filter_var(
            getenv('ATOM_ACTIVATE_OIDC_PLUGIN'),
            \FILTER_VALIDATE_BOOLEAN,
        );

        return $markerExists || $environmentEnabled;
    }
}
