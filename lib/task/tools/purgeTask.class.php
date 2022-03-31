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
 * Purge AtoM data.
 *
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class purgeTask extends installTask
{
    /**
     * @see sfTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    public function execute($arguments = [], $options = [])
    {
        $this->createSfContext();
        $this->validateOptions($options);

        if ($options['demo']) {
            $this->setDemoOptions($options);
        }

        if ($options['use-gitconfig']) {
            $this->getGitConfigData($options);
        }

        $this->initializeDbAndEs($options);

        $this->logSection($this->name, 'Adding site configuration');
        $this->addSiteConfiguration($options);

        $this->logSection($this->name, 'Creating admin user');
        $this->addAdminUser($options);

        $this->logSection($this->name, 'Purge completed');
    }

    /**
     * @see sfTask
     */
    protected function configure()
    {
        $this->addArguments([
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('use-gitconfig', null, sfCommandOption::PARAMETER_NONE, 'Get username and email from $HOME/.gitconfig'),
            new sfCommandOption('title', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site title'),
            new sfCommandOption('description', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site description'),
            new sfCommandOption('url', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired site base URL'),
            new sfCommandOption('username', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin username'),
            new sfCommandOption('email', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin email address'),
            new sfCommandOption('password', null, sfCommandOption::PARAMETER_OPTIONAL, 'Desired admin password'),
            new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
            new sfCommandOption('demo', null, sfCommandOption::PARAMETER_NONE, 'Use default demo values, do not ask for confirmation'),
            new sfCommandOption(
                'culture',
                null,
                sfCommandOption::PARAMETER_OPTIONAL,
                'Desired culture (e.g. "fr") for site title and description',
            ),
        ]);

        $this->namespace = 'tools';
        $this->name = 'purge';
        $this->briefDescription = 'Purge all data.';

        $this->detailedDescription = <<<'EOF'
Purge all data.
EOF;
    }

    protected function createSfContext()
    {
        sfConfig::set('app_avoid_routing_propel_exceptions', true);

        $this->configuration = ProjectConfiguration::getApplicationConfiguration(
            'qubit',
            'cli',
            false
        );

        $this->context = sfContext::createInstance($this->configuration);
    }

    protected function validateOptions($options)
    {
        if (!$options['demo'] && !function_exists('readline')) {
            $needed = ['title', 'description', 'url', 'email', 'username', 'password'];
            if (!array_key_exists($needed, $options)) {
                throw new Exception(
                    'At least one of the following command line '
                    .'options is missing: title, description, url, email, username '
                    .'and/or password.'
                );
            }
        }
    }

    protected function setDemoOptions(&$options)
    {
        $options['no-confirmation'] = true;
        $options['title'] = 'Demo site';
        $options['description'] = 'Demo site';
        $options['email'] = 'demo@example.com';
        $options['username'] = 'demo';
        $options['password'] = 'demo';
        $options['url'] = 'http://127.0.0.1';
        $options['culture'] = 'en';
    }

    /**
     * TODO: Fix me!
     */
    protected function getGitConfigData()
    {
        // attempt to provide default user admin name and email
        if ($_SERVER['HOME']) {
            $gitConfigFile = $_SERVER['HOME'].'/.gitconfig';

            if (file_exists($gitConfigFile)) {
                $gitConfig = parse_ini_file($gitConfigFile);

                $defaultUser = strtolower(strtok($gitConfig['name'], ' '));
                $defaultEmail = $gitConfig['email'];
            }
        }
    }

    protected function addSiteConfiguration($options)
    {
        $siteTitle = $this->getConfigValue(
            $options['title'],
            sprintf('(%s) Site title', $this->getCulture($options)),
            'AtoM'
        );

        $siteDescription = $this->getConfigValue(
            $options['description'],
            sprintf('(%s) Site description', $this->getCulture($options)),
            'Test site'
        );

        $siteBaseUrl = $this->getConfigValue(
            $options['url'], 'Site base URL', 'http://127.0.0.1'
        );

        arInstall::createSetting(
            'siteTitle', $siteTitle, $this->getCultureOptions($options)
        );

        arInstall::createSetting(
            'siteDescription',
            $siteDescription,
            $this->getCultureOptions($options)
        );

        $validator = new sfValidatorUrl(['protocols' => ['http', 'https']]);
        $validator->clean($siteBaseUrl);
        arInstall::createSetting('siteBaseUrl', $siteBaseUrl);
    }

    protected function getConfigValue($value, $prompt, $default)
    {
        if (!empty($value)) {
            return $value;
        }

        $response = readline(sprintf('%s [%s]: ', $prompt, $default));

        return !empty($response) ? $response : $default;
    }

    protected function addAdminUser($options)
    {
        addSuperuserTask::addSuperUser($options['username'], $options);
    }

    protected function getCulture($options)
    {
        return $options['culture'] ?? sfConfig::get('sf_default_culture');
    }

    protected function getCultureOptions($options)
    {
        $culture = $this->getCulture($options);

        return ['sourceCulture' => $culture, 'culture' => $culture];
    }
}
