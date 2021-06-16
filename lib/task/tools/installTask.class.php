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

class installTask extends sfBaseTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $rootDir = sfConfig::get('sf_root_dir');
        $this->clearConfigFiles($rootDir, $options);
        $finalOptions = $this->getFinalOptions($options);
        $this->createConfigFiles($finalOptions);
        $this->reloadConfig($rootDir);
        $this->testConfig($finalOptions);
        $this->initializeDbAndEs($options);

        $this->logSection($this->name, 'Adding site configuration');

        foreach ($finalOptions['site'] as $name => $value) {
            arInstall::createSetting($name, $value);
        }

        $this->logSection($this->name, 'Creating admin user');

        addSuperuserTask::addSuperUser(
            $finalOptions['admin']['username'],
            $finalOptions['admin']
        );

        $this->logSection($this->name, 'Installation completed');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addOptions([
            new sfCommandOption(
                'application',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'The application name',
                'qubit'
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
            new sfCommandOption(
                'database-host',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Database host'
            ),
            new sfCommandOption(
                'database-port',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Database port'
            ),
            new sfCommandOption(
                'database-name',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Database name'
            ),
            new sfCommandOption(
                'database-user',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Database user'
            ),
            new sfCommandOption(
                'database-password',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Database password'
            ),
            new sfCommandOption(
                'search-host',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Search host'
            ),
            new sfCommandOption(
                'search-port',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Search port'
            ),
            new sfCommandOption(
                'search-index',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Search index'
            ),
            new sfCommandOption(
                'site-title',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Site title'
            ),
            new sfCommandOption(
                'site-description',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Site description'
            ),
            new sfCommandOption(
                'site-base-url',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Site base URL'
            ),
            new sfCommandOption(
                'admin-email',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Admin email'
            ),
            new sfCommandOption(
                'admin-username',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Admin username'
            ),
            new sfCommandOption(
                'admin-password',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Admin password'
            ),
            new sfCommandOption(
                'demo',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Use default demo values'
            ),
            new sfCommandOption(
                'no-confirmation',
                null,
                sfCommandOption::PARAMETER_NONE,
                'Do not ask for confirmation'
            ),
        ]);

        $this->namespace = 'tools';
        $this->name = 'install';
        $this->briefDescription = 'Install AtoM.';
        $this->detailedDescription = <<<'EOF'
Configure and initialize a new AtoM instance:

- Configure database.
- Configure search.
- Configure site.
- Configure admin user.
- Initialize database.
- Load initial data.
- Create search index.
- Add site configuration.
- Create admin user.
EOF;
    }

    protected function initializeDbAndEs($options)
    {
        $this->logSection($this->name, 'Initializing database');

        $insertSql = new sfPropelInsertSqlTask(
            $this->dispatcher,
            $this->formatter
        );
        $ret = $insertSql->run(
            [],
            ['no-confirmation' => $options['no-confirmation']]
        );

        // Stop when the insert SQL task is aborted
        if ($ret) {
            $this->logSection($this->name, 'Aborted');

            exit(1);
        }

        arInstall::modifySql();

        $this->logSection($this->name, 'Loading initial data');

        arInstall::loadData($this->dispatcher, $this->formatter);

        $this->logSection($this->name, 'Creating search index');

        arInstall::populateSearchIndex();
    }

    private function clearConfigFiles($rootDir, $options)
    {
        $this->logSection($this->name, 'Checking configuration files');

        $configFiles = [
            $rootDir.'/apps/qubit/config/settings.yml',
            $rootDir.'/config/config.php',
            $rootDir.'/config/databases.yml',
            $rootDir.'/config/propel.ini',
            $rootDir.'/config/search.yml',
        ];

        $existingConfigFiles = [];
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $existingConfigFiles[] = $file;
            }
        }

        if (count($existingConfigFiles) > 0) {
            if (
                !$options['no-confirmation']
                && !$this->askConfirmation(array_merge(
                    [
                        'WARNING: The following configuration files already',
                        '         exist and will be overwritten!',
                        '',
                    ],
                    $existingConfigFiles,
                    ['', 'Would you like to continue? (y/N)']),
                    'QUESTION_LARGE',
                    false
                )
            ) {
                $this->logSection($this->name, 'Aborted');

                exit(1);
            }

            $this->logSection($this->name, 'Deleting configuration files');

            $deletionFailures = [];
            foreach ($configFiles as $file) {
                if (file_exists($file) && !unlink($file)) {
                    $deletionFailures[] = $file;
                }
            }

            if (count($deletionFailures) > 0) {
                $this->logBlock(array_merge(
                    [
                        '',
                        "The following configuration files can't be deleted:",
                        '',
                    ],
                    $deletionFailures,
                    ['']
                ), 'ERROR');

                exit(1);
            }
        }
    }

    private function getFinalOptions($options)
    {
        $this->logSection($this->name, 'Configure database');

        $databaseOptions = [
            'databaseHost' => $this->getOptionValue(
                'database-host',
                $options,
                'Database host',
                'localhost'
            ),
            'databasePort' => $this->getOptionValue(
                'database-port',
                $options,
                'Database port',
                '3306'
            ),
            'databaseName' => $this->getOptionValue(
                'database-name',
                $options,
                'Database name',
                'atom'
            ),
            'databaseUsername' => $this->getOptionValue(
                'database-user',
                $options,
                'Database user',
                'atom'
            ),
            'databasePassword' => $this->getOptionValue(
                'database-password',
                $options,
                'Database password'
            ),
        ];

        $this->logSection($this->name, 'Configure search');

        $searchOptions = [
            'searchHost' => $this->getOptionValue(
                'search-host',
                $options,
                'Search host',
                'localhost'
            ),
            'searchPort' => $this->getOptionValue(
                'search-port',
                $options,
                'Search port',
                '9200'
            ),
            'searchIndex' => $this->getOptionValue(
                'search-index',
                $options,
                'Search index',
                'atom'
            ),
        ];

        if ($options['demo']) {
            $this->logSection($this->name, 'Setting demo options');

            $siteOptions = [
                'siteTitle' => 'Demo site',
                'siteDescription' => 'Demo site',
                'siteBaseUrl' => 'http://127.0.0.1',
            ];
            $adminOptions = [
                'email' => 'demo@example.com',
                'username' => 'demo',
                'password' => 'demo',
            ];
        } else {
            $this->logSection($this->name, 'Configure site');

            $siteOptions = [
                'siteTitle' => $this->getOptionValue(
                    'site-title',
                    $options,
                    'Site title',
                    'AtoM'
                ),
                'siteDescription' => $this->getOptionValue(
                    'site-description',
                    $options,
                    'Site description',
                    'Access to Memory'
                ),
                'siteBaseUrl' => $this->getOptionValue(
                    'site-base-url',
                    $options,
                    'Site base URL',
                    'http://127.0.0.1',
                    new sfValidatorUrl(['protocols' => ['http', 'https']]),
                ),
            ];

            $this->logSection($this->name, 'Configure admin user');

            $adminOptions = [
                'email' => $this->getOptionValue(
                    'admin-email',
                    $options,
                    'Admin email',
                    null,
                    new sfValidatorEmail(),
                ),
                'username' => $this->getOptionValue(
                    'admin-username',
                    $options,
                    'Admin username',
                ),
                'password' => $this->getOptionValue(
                    'admin-password',
                    $options,
                    'Admin password',
                ),
            ];
        }

        $this->logSection($this->name, 'Confirm configuration');

        echo "Database host       {$databaseOptions['databaseHost']}\n";
        echo "Database port       {$databaseOptions['databasePort']}\n";
        echo "Database name       {$databaseOptions['databaseName']}\n";
        echo "Database user       {$databaseOptions['databaseUsername']}\n";
        echo "Database password   {$databaseOptions['databasePassword']}\n";
        echo "Search host         {$searchOptions['searchHost']}\n";
        echo "Search port         {$searchOptions['searchPort']}\n";
        echo "Search index        {$searchOptions['searchIndex']}\n";
        echo "Site title          {$siteOptions['siteTitle']}\n";
        echo "Site description    {$siteOptions['siteDescription']}\n";
        echo "Site base URL       {$siteOptions['siteBaseUrl']}\n";
        echo "Admin email         {$adminOptions['email']}\n";
        echo "Admin username      {$adminOptions['username']}\n";
        echo "Admin password      {$adminOptions['password']}\n";

        if (
            !$options['no-confirmation']
            && !$this->askConfirmation(
                ['Confirm configuration and continue? (y/N)'],
                'QUESTION_LARGE',
                false
            )
        ) {
            $this->logSection($this->name, 'Aborted');

            exit(1);
        }

        return [
            'database' => $databaseOptions,
            'search' => $searchOptions,
            'site' => $siteOptions,
            'admin' => $adminOptions,
        ];
    }

    private function createConfigFiles($options)
    {
        $this->logSection($this->name, 'Setting configuration');

        try {
            arInstall::createDirectories();
            arInstall::checkWritablePaths();
            arInstall::createDatabasesYml();
            arInstall::createPropelIni();
            arInstall::createSettingsYml();
            arInstall::createSfSymlink();
            arInstall::configureDatabase($options['database']);
            arInstall::configureSearch($options['search']);
        } catch (Exception $e) {
            $this->logBlock(
                [
                    '',
                    $e->getMessage(),
                    '',
                ],
                'ERROR'
            );

            exit(1);
        }
    }

    private function reloadConfig($rootDir)
    {
        $cacheClear = new sfCacheClearTask(
            $this->dispatcher,
            $this->formatter
        );
        $cacheClear->run();
        Propel::configure($rootDir.'/config/config.php');
        Propel::setDefaultDB('propel');
        sfConfig::set('app_avoid_routing_propel_exceptions', true);
        $this->configuration = ProjectConfiguration::getApplicationConfiguration(
            'qubit',
            'cli',
            false
        );
        $this->context = sfContext::createInstance($this->configuration);
        $this->context->databaseManager->loadConfiguration();
        arElasticSearchPluginConfiguration::reloadConfig(
            $this->context->getConfiguration()
        );
    }

    private function testConfig($options)
    {
        try {
            $this->context->getDatabaseConnection('propel');
        } catch (Exception $e) {
            $this->logBlock(
                [
                    '',
                    'Database connection failure:',
                    '',
                    $e->getMessage(),
                    '',
                ],
                'ERROR'
            );

            exit(1);
        }

        try {
            arInstall::checkSearchConnection($options['search']);
        } catch (Exception $e) {
            $this->logBlock(
                [
                    '',
                    'Elasticsearch connection failure:',
                    '',
                    $e->getMessage(),
                    '',
                ],
                'ERROR'
            );

            exit(1);
        }
    }

    private function getOptionValue(
        $name,
        $options,
        $prompt,
        $default = null,
        $validator = null
    ) {
        if ($options[$name]) {
            $value = $options[$name];
        } else {
            if ($default) {
                $prompt .= " [{$default}]";
            }

            $value = readline($prompt.': ');
            $value = $value ? trim($value) : $default;
        }

        if (!$value) {
            throw new Exception("{$prompt} is required.");
        }

        if ($validator) {
            try {
                $validator->clean($value);
            } catch (sfValidatorError $e) {
                throw new Exception("'{$value}' is invalid.");
            }
        }

        return $value;
    }
}
