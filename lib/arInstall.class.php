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

class arInstall
{
    public static function createDirectories()
    {
        if (!is_dir(sfConfig::get('sf_log_dir'))) {
            mkdir(sfConfig::get('sf_log_dir'), 0777, true);
        }

        Qubit::createUploadDirsIfNeeded();
        Qubit::createDownloadsDirIfNeeded();
    }

    public static function checkWritablePaths()
    {
        $finder = sfFinder::type('any');

        $pathsToCheck = [
            sfConfig::get('sf_cache_dir'),
            sfConfig::get('sf_data_dir'),
            sfConfig::get('sf_log_dir'),
            sfConfig::get('sf_upload_dir'),
            sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.'downloads',
        ];

        foreach ($pathsToCheck as $path) {
            // sfFinder::in() does not include the argument path
            if (!is_writable($path)) {
                throw new Exception("Can't write in {$path}");
            }

            foreach ($finder->in($path) as $path) {
                if (!is_writable($path)) {
                    throw new Exception("Can't write in {$path}");
                }
            }
        }
    }

    public static function createDatabasesYml()
    {
        $databasesYmlPath = sfConfig::get('sf_config_dir').'/databases.yml';

        // Read databases.yml contents from existing databases.yml,
        // databases.yml.tmpl (for a git checkout), or symfony
        // skeleton databases.yml, whichever is found first.
        $databasesYmlPaths = [];
        $databasesYmlPaths[] = $databasesYmlPath;
        $databasesYmlPaths[] = $databasesYmlPath.'.tmpl';
        $databasesYmlPaths[] = sfConfig::get('sf_lib_dir')
            .'/task/generator/skeleton/project/config/databases.yml';

        foreach ($databasesYmlPaths as $path) {
            if (false !== $content = file_get_contents($path)) {
                break;
            }
        }

        if (false === file_put_contents($databasesYmlPath, $content)) {
            throw new Exception(
                "Can't write configuration file {$databasesYmlPath}"
            );
        }
    }

    public static function createPropelIni()
    {
        $propelIniPath = sfConfig::get('sf_config_dir').'/propel.ini';

        // Read propel.ini contents from existing propel.ini,
        // propel.ini.tmpl (for a git checkout), or symfony
        // skeleton propel.ini, whichever is found first.
        $propelIniPaths = [];
        $propelIniPaths[] = $propelIniPath;
        $propelIniPaths[] = $propelIniPath.'.tmpl';
        $propelIniPaths[] = sfConfig::get('sf_lib_dir')
            .'/task/generator/skeleton/project/config/propel.ini';

        foreach ($propelIniPaths as $path) {
            if (false !== $content = file_get_contents($path)) {
                break;
            }
        }

        if (false === file_put_contents($propelIniPath, $content)) {
            throw new Exception(
                "Can't write configuration file {$propelIniPath}"
            );
        }
    }

    public static function createSettingsYml()
    {
        $settingsYmlPath = sfConfig::get('sf_app_config_dir').'/settings.yml';

        // Read settings.yml contents from existing settings.yml,
        // settings.yml.tmpl (for a git checkout), or symfony
        // skeleton settings.yml, whichever is found first.
        $settingsYmlPaths = [];
        $settingsYmlPaths[] = $settingsYmlPath;
        $settingsYmlPaths[] = $settingsYmlPath.'.tmpl';
        $settingsYmlPaths[] = sfConfig::get('sf_lib_dir')
            .'/task/generator/skeleton/app/app/config/settings.yml';

        foreach ($settingsYmlPaths as $path) {
            if (false !== $content = file_get_contents($path)) {
                break;
            }
        }

        // Generate and set CSRF secret
        $content = str_replace(
            'change_me',
            bin2hex(openssl_random_pseudo_bytes(16)),
            $content
        );
        if (false === file_put_contents($settingsYmlPath, $content)) {
            throw new Exception(
                "Can't write configuration file {$settingsYmlPath}"
            );
        }
    }

    public static function createSfSymlink()
    {
        $file = sfConfig::get('sf_root_dir').'/vendor/symfony/data/web/sf';
        $link = sfConfig::get('sf_root_dir').'/sf';
        if (!file_exists($link) && false === symlink($file, $link)) {
            throw new Exception("Can't write sf symlink");
        }
    }

    public static function configureDatabase($options)
    {
        $dsn = 'mysql:dbname='.$options['databaseName'];

        if (
            isset($options['databaseHost'])
            && 'localhost' != $options['databaseHost']
        ) {
            $dsn .= ';host='.$options['databaseHost'];
        }

        if (isset($options['databasePort'])) {
            $dsn .= ';port='.$options['databasePort'];
        }

        $config = [
            'all' => [
                'propel' => [
                    'class' => 'sfPropelDatabase',
                    'param' => [
                        'encoding' => 'utf8mb4',
                        'persistent' => true,
                        'pooling' => true,
                        'dsn' => $dsn,
                        'username' => $options['databaseUsername'],
                        'password' => $options['databasePassword'],
                    ],
                ],
            ],
            'dev' => [
                'propel' => [
                    'param' => [
                        'classname' => 'DebugPDO',
                        'debug' => [
                            'realmemoryusage' => true,
                            'details' => [
                                'time' => ['enabled' => true],
                                'slow' => [
                                    'enabled' => true,
                                    'threshold' => 0.1,
                                ],
                                'mem' => ['enabled' => true],
                                'mempeak' => ['enabled' => true],
                                'memdelta' => ['enabled' => true],
                            ],
                        ],
                    ],
                ],
            ],
            'test' => [
                'propel' => [
                    'param' => [
                        'classname' => 'DebugPDO',
                    ],
                ],
            ],
        ];

        $configFile = sfConfig::get('sf_config_dir').'/config.php';
        $content = sprintf(
            "<?php\n"
            ."// auto-generated by arInstall::configureDatabase()\n"
            ."// date: %s\n\nreturn %s;\n",
            date('Y/m/d H:i:s'),
            var_export($config, true)
        );
        if (false === file_put_contents($configFile, $content)) {
            throw new Exception(
                "Can't write configuration file {$configFile}"
            );
        }
    }

    public static function configureSearch($options)
    {
        $defaults = sfConfig::get('sf_plugins_dir')
            .'/arElasticSearchPlugin/config/search.yml';
        $config = arElasticSearchConfigHandler::getConfiguration([$defaults]);

        if (isset($options['searchHost'])) {
            $config['server']['host'] = $options['searchHost'];
        } else {
            $config['server']['host'] = 'localhost';
        }

        if (isset($options['searchPort'])) {
            $config['server']['port'] = $options['searchPort'];
        } else {
            $config['server']['port'] = '9200';
        }

        if (isset($options['searchIndex'])) {
            $config['index']['name'] = $options['searchIndex'];
        } else {
            $config['index']['name'] = 'atom';
        }

        $env = [];
        $env['all'] = $config;

        $configFile = sfConfig::get('sf_config_dir').'/search.yml';
        if (false === file_put_contents($configFile, sfYaml::dump($env, 9))) {
            throw new Exception(
                "Can't write configuration file {$configFile}"
            );
        }
    }

    public static function checkSearchConnection($options)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, sprintf(
            'http://%s',
            $options['searchHost']
        ));
        curl_setopt($curl, CURLOPT_PORT, $options['searchPort']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        ob_start();
        curl_exec($curl);
        ob_get_clean();
        if (0 < curl_errno($curl)) {
            throw new Exception(sprintf(
                "Can't connect to the server (%s).",
                curl_error($curl)
            ));
        }

        $curlHttpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if (200 !== $curlHttpCode) {
            throw new Exception("Elasticsearch error: {$curlHttpCode}");
        }
    }

    public static function modifySql()
    {
        // Propel version is unable to set column collation from schema.yml.
        // Keep PAD SPACE `utf8mb4_bin` (instead of new `utf8mb4_0900_bin`).
        $sql = 'ALTER TABLE `slug` MODIFY `slug` VARCHAR(255)
            CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL';
        QubitPdo::modify($sql);
    }

    public static function loadData($dispatcher, $formatter)
    {
        QubitSearch::disable();

        $object = new QubitInformationObject();
        $object->id = QubitInformationObject::ROOT_ID;
        $object->indexOnSave = false;
        $object->save();

        $object = new QubitActor();
        $object->id = QubitActor::ROOT_ID;
        $object->indexOnSave = false;
        $object->save();

        $object = new QubitRepository();
        $object->id = QubitRepository::ROOT_ID;
        $object->indexOnSave = false;
        $object->save();

        $object = new QubitSetting();
        $object->name = 'plugins';
        $object->value = serialize([
            'sfDcPlugin',
            'arDominionB5Plugin',
            'sfEacPlugin',
            'sfEadPlugin',
            'sfIsaarPlugin',
            'sfIsadPlugin',
            'arDacsPlugin',
            'sfIsdfPlugin',
            'sfIsdiahPlugin',
            'sfModsPlugin',
            'sfRadPlugin',
            'sfSkosPlugin',
        ]);
        $object->save();

        $loadData = new sfPropelDataLoadTask($dispatcher, $formatter);
        $loadData->run();

        $premisAccessRightValues = [];
        foreach (
            QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item
        ) {
            $premisAccessRightValues[$item->slug] = [
                'allow_master' => 1,
                'allow_reference' => 1,
                'allow_thumb' => 1,
                'conditional_master' => 0,
                'conditional_reference' => 1,
                'conditional_thumb' => 1,
                'disallow_master' => 0,
                'disallow_reference' => 0,
                'disallow_thumb' => 0,
            ];
        }
        $setting = new QubitSetting();
        $setting->name = 'premisAccessRightValues';
        $setting->sourceCulture = sfConfig::get('sf_default_culture');
        $setting->setValue(serialize(
            $premisAccessRightValues),
            ['sourceCulture' => true]
        );
        $setting->save();

        $i18n = sfContext::getInstance()->i18n;
        $accessDisallowWarning = $i18n->__(
            'Access to this record is restricted because it contains personal or confidential information. Please contact the Reference Archivist for more information on accessing this record.'
        );
        $accessConditionalWarning = $i18n->__(
            'This record has not yet been reviewed for personal or confidential information. Please contact the Reference Archivist to request access and initiate an access review.'
        );

        foreach (
            QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item
        ) {
            $setting = new QubitSetting();
            $setting->name = "{$item->slug}_disallow";
            $setting->scope = 'access_statement';
            $setting->setValue($accessDisallowWarning, ['culture' => 'en']);

            foreach (
                QubitI18N::getTranslations($accessDisallowWarning) as $langCode => $message
            ) {
                $setting->setValue($message, ['culture' => $langCode]);
            }

            $setting->save();

            $setting = new QubitSetting();
            $setting->name = "{$item->slug}_conditional";
            $setting->scope = 'access_statement';
            $setting->setValue($accessConditionalWarning, ['culture' => 'en']);

            foreach (
                QubitI18N::getTranslations($accessConditionalWarning) as $langCode => $message
            ) {
                $setting->setValue($message, ['culture' => $langCode]);
            }

            $setting->save();
        }
    }

    public static function populateSearchIndex()
    {
        sfConfig::add(QubitSetting::getSettingsArray());
        QubitSearch::enable();
        QubitSearch::getInstance()->populate();
    }

    public static function createSetting($name, $value, $options = [])
    {
        $setting = QubitSetting::createNewSetting($name, $value, $options);
        $setting->save();
    }
}
