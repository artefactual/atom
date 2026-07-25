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

namespace Atom\Framework\Configuration;

final readonly class DirectoryParameters
{
    private string $projectDirectory;

    public function __construct(
        string $projectDirectory,
        private string $application,
        private string $environment,
        private bool $debug,
    ) {
        $this->projectDirectory = rtrim($projectDirectory, '/\\');
    }

    public function all(): array
    {
        $applicationDirectory = $this->projectDirectory
            .'/apps/'.$this->application;
        $applicationCacheDirectory = $this->projectDirectory
            .'/cache/'.$this->application.'/'.$this->environment;

        return [
            'sf_root_dir' => $this->projectDirectory,
            'sf_apps_dir' => $this->projectDirectory.'/apps',
            'sf_lib_dir' => $this->projectDirectory.'/lib',
            'sf_log_dir' => $this->projectDirectory.'/log',
            'sf_data_dir' => $this->projectDirectory.'/data',
            'sf_config_dir' => $this->projectDirectory.'/config',
            'sf_test_dir' => $this->projectDirectory.'/test',
            'sf_plugins_dir' => $this->projectDirectory.'/plugins',
            'sf_web_dir' => $this->projectDirectory,
            'sf_upload_dir' => $this->projectDirectory.'/uploads',
            'sf_app' => $this->application,
            'sf_environment' => $this->environment,
            'sf_debug' => $this->debug,
            'sf_app_dir' => $applicationDirectory,
            'sf_app_config_dir' => $applicationDirectory.'/config',
            'sf_app_i18n_dir' => $applicationDirectory.'/i18n',
            'sf_app_lib_dir' => $applicationDirectory.'/lib',
            'sf_app_module_dir' => $applicationDirectory.'/modules',
            'sf_app_template_dir' => $applicationDirectory.'/templates',
            'sf_cache_dir' => $this->projectDirectory.'/cache',
            'sf_app_cache_dir' => $applicationCacheDirectory,
            'sf_config_cache_dir' => $applicationCacheDirectory.'/config',
            'sf_i18n_cache_dir' => $applicationCacheDirectory.'/i18n',
            'sf_template_cache_dir' => $applicationCacheDirectory.'/template',
            'sf_test_cache_dir' => $applicationCacheDirectory.'/test',
        ];
    }
}
