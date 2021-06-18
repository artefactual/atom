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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Builds the CSS in plugins containing a `css/main.less` file.
 * This task requires the NPM dependecies installed.
 */
class buildThemesCssTask extends sfBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $configuration = ProjectConfiguration::getApplicationConfiguration(
            'qubit',
            'cli',
            false
        );

        foreach ($configuration->getAllPluginPaths() as $name => $path) {
            $cssPath = $path.DIRECTORY_SEPARATOR.'css';
            $lessPath = $cssPath.DIRECTORY_SEPARATOR.'main.less';
            if (file_exists($lessPath)) {
                echo "Building {$name} CSS file.\n";

                $cmd = sprintf(
                    'npx lessc --rewrite-urls=all --clean-css %s %s',
                    $lessPath,
                    $cssPath.DIRECTORY_SEPARATOR.'min.css'
                );
                exec($cmd, $output, $exitCode);

                if (0 != $exitCode) {
                    echo "Building {$name} CSS file failed.\n";
                }
            }
        }
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
        ]);

        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                true
            ),
            new sfCommandOption(
                'env',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The environment',
                'cli'
            ),
            new sfCommandOption(
                'connection',
                null,
                sfCommandOption::PARAMETER_REQUIRED,
                'The connection name',
                'propel'
            ),
        ]);

        $this->namespace = 'tools';
        $this->name = 'build-css';
        $this->briefDescription = 'Builds plugin\'s CSS files.';

        $this->detailedDescription = <<<'EOF'
Builds the CSS in plugins containing a `css/main.less` file.
This task requires the NPM dependecies installed.
EOF;
    }
}
