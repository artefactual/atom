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
 * Migrate qubit data model via direct SQL calls.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitUpgradeSqlTask extends sfBaseTask
{
    protected $initialVersion;
    protected $pluginsSetting;

    /**
     * @see sfBaseTask
     */
    protected function configure()
    {
        $this->namespace = 'tools';
        $this->name = 'upgrade-sql';
        $this->briefDescription = 'Migrate the database schema and existing data for compatibility with a newer version of Qubit.';
        $this->detailedDescription = <<<'EOF'
The [tools:migrate|INFO] task modifies the SQL data structure for compatibility with later versions of the application:

  [./symfony tools:upgrade-sql|INFO]
EOF;

        $this->addArguments([
            new sfCommandArgument('target', sfCommandArgument::OPTIONAL, 'Target version'),
        ]);

        $this->addOptions([
            new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
            new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
            new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
            new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
            new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'Verbose mode', null),
            new sfCommandOption('number', 'n', sfCommandOption::PARAMETER_OPTIONAL, 'Run only specific migration number(s) (separated by commas if multiple)', null),
            new sfCommandOption('reset-static-pages', 's', sfCommandOption::PARAMETER_NONE, 'Reset title and content, of static pages created by AtoM, to current defaults', null),
        ]);

        // Disable plugin loading from plugins/ before this task.
        // Using command.pre_command to ensure that it happens early enough.
        $this->dispatcher->connect('command.pre_command', function ($e) {
            if (!$e->getSubject() instanceof self) {
                return;
            }

            sfPluginAdminPluginConfiguration::$loadPlugins = false;
        });
    }

    /**
     * @see sfBaseTask
     *
     * @param mixed $arguments
     * @param mixed $options
     */
    protected function execute($arguments = [], $options = [])
    {
        $dbManager = new sfDatabaseManager($this->configuration);
        $database = $dbManager->getDatabase($options['connection']);

        sfContext::createInstance($this->configuration);

        // Handle option to reset static page translations
        $this->handleStaticPageTranslationResetOption($options);

        $this->getPluginSettings();
        $this->removeMissingPluginsFromSettings();
        $this->checkMissingThemes();

        // Deactivate search index, must be rebuilt later anyways
        QubitSearch::disable();

        // Get initial version
        $sql = 'SELECT value AS version
            FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
            WHERE name = "version";';

        $this->initialVersion = QubitPdo::fetchColumn($sql);

        // A bug in the migration script for Release 1.1 left the version=62
        // instead of 75, so we need to check if the user is upgrading from 1.1
        // or 1.2
        if (62 == intval($this->initialVersion)) {
            // Check if "accession_mask" setting exists (added in version 63)
            $sql = "SELECT id FROM setting WHERE name='accession_mask';";
            if (false !== QubitPdo::fetchOne($sql)) {
                $this->initialVersion = 75;
            }
        }

        // Use old migration script for versions before 62
        if (null == $this->initialVersion || 62 > $this->initialVersion) {
            $this->logBlock(
                [
                    '',
                    'Please use the propel:migrate task for upgrading',
                    'from Qubit releases prior to Release 1.1',
                    '',
                ],
                'ERROR'
            );

            return 1;
        }

        // Confirmation
        if (
            !$options['no-confirmation']
            && !$this->askConfirmation(
                [
                    'WARNING: Your database has not been backed up!',
                    'Please back-up your database manually before you proceed.',
                    'If this task fails you may lose your data.',
                    '',
                    'Have you done a manual backup and wish to proceed? (y/N)',
                ],
                'QUESTION_LARGE',
                false
            )
        ) {
            $this->logSection('upgrade-sql', 'Task aborted.');

            return 1;
        }

        // Modify sql_mode for the migration process.
        // MySQL 5.7 included STRICT_TRANS_TABLES in the sql_mode by default and,
        // during the migration process, the database and the model declaration
        // are not totally in sync. For example, the removal of the nested from the
        // QubitTaxonomy model caused problems in the upgrade, as new taxonomies
        // are created in the migrations without lft and rgt values while the
        // database is expecting those columns.
        $sqlMode = QubitPdo::fetchColumn('SELECT @@sql_mode');
        QubitPdo::modify("SET sql_mode='ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'");

        // Find all the upgrade classes in lib/task/migrate
        $version = $this->initialVersion;

        if (!empty($options['number'])) {
            $this->runSpecificMigrations(explode(',', $options['number']));
        } else {
            foreach (
                sfFinder::type('file')
                    ->maxdepth(0)
                    ->sort_by_name()
                    ->name('arUpgrader*.class.php')
                    ->in(sfConfig::get('sf_lib_dir').'/task/migrate') as $filename
            ) {
                $className = preg_replace('/.*(arUpgrader\d+).*/', '$1', $filename);
                $class = new $className();

                if ($class::INIT_VERSION > $version) {
                    continue;
                }

                try {
                    if ($options['verbose']) {
                        $this->logSection('upgrade-sql', sprintf('Upgrading from Release %s', $class::MILESTONE));
                    }

                    while ($class->up($version, $this->configuration, $options)) {
                        // Update version in database
                        $this->updateDatabaseVersion(++$version);
                    }
                } catch (Exception $e) {
                    $this->logSection('upgrade-sql', sprintf('The task failed while trying to upgrade to v%s', $version + 1));

                    throw $e;
                }
            }

            // The database version in Release 1.3 was v92. After that, AtoM was forked
            // in two different versions: 1.x and 2.x. Both will be maintained for now.
            // This task selectively upgrades the user to the latest database version
            // available for its milestone. In order to keep things simpler, db upgrades
            // won't be able to target 1.x only. Upgrades targetting only 2.x won't be
            // applied to 1.x users until they decide to migrate to AtoM 2.x.
            // See #4494 for more details.

            // The milestone founds in the database before the upgrade happens
            $previousMilestone = $this->getPreviousMilestone();

            // Get current codebase milestone
            $substrings = preg_split('/\./', qubitConfiguration::VERSION);
            $currentMilestone = array_shift($substrings);

            // Upgrades post to Release 1.3 (v92) are located under
            // task/migrate/migrations and named using the following format:
            // "arMigration%04d.class.php" (the first one is arMigration0093.class.php)
            $version = $this->runMigrationsInDirectory(
                $version,
                sfConfig::get('sf_lib_dir').'/task/migrate/migrations',
                $previousMilestone,
                $currentMilestone,
                $options['verbose']
            );
        }

        // Restore sql_mode
        QubitPdo::modify("SET sql_mode='{$sqlMode}'");

        // Analyze tables:
        // - Performs and stores a key distribution analysis (if the table
        //   has not changed since the last one, its not analyzed again).
        // - Determines index cardinality, used for join optimizations.
        // - Removes the table from the definition cache.
        foreach (QubitPdo::fetchAll('SHOW TABLES;', [], ['fetchMode' => PDO::FETCH_COLUMN]) as $table) {
            QubitPdo::modify(sprintf('ANALYZE TABLE `%s`;', $table));
        }

        // Delete cache files (for menus, etc.)
        foreach (sfFinder::type('file')->name('*.cache')->in(sfConfig::get('sf_cache_dir')) as $cacheFile) {
            unlink($cacheFile);
        }

        // Do standard cache clear
        $cacheClear = new sfCacheClearTask(sfContext::getInstance()->getEventDispatcher(), new sfAnsiColorFormatter());
        $cacheClear->run();

        // If not running specific migrations individually, store the milestone in
        // settings as we're going to need that in further upgrades!
        // Use case: a user running 1.x for a long period after 2.x release, then upgrades
        if (empty($options['number'])) {
            $this->updateMilestone();
        }

        // Clear settings cache to reload them in sfConfig on the first request
        // after the upgrade in QubitSettingsFilter.
        QubitCache::getInstance()->removePattern('settings:i18n:*');

        $this->logSection('upgrade-sql', sprintf('Successfully upgraded to Release %s v%s', qubitConfiguration::VERSION, $version));
    }

    protected function handleStaticPageTranslationResetOption($options)
    {
        // Attempt to reset static page translations, if requested and confirmed
        if (
            $options['reset-static-pages']
            && (
                $options['no-confirmation']
                || $this->askConfirmation(
                    [
                        'Are you sure you would like to reset your static page '.
                        "content to this AtoM version's default content? (y/N)",
                    ],
                    'QUESTION_LARGE',
                    false
                )
            )
        ) {
            $this->recreateMissingStaticPages($options);

            $changeCount = count($this->resetOrCheckForChangedStaticPages(true));

            if (empty($changeCount)) {
                $this->logSection('upgrade-sql', "Static pages in database already match this AtoM version's.");
            } else {
                $this->resetOrCheckForChangedStaticPages();

                $this->logSection('upgrade-sql', sprintf('Reset %d static page(s).', $changeCount));
            }
        }
    }

    protected function recreateMissingStaticPages($options)
    {
        // Ignore missing static pages when no confirmation mode is on so it has to be approved manually
        if ($options['no-confirmation']) {
            return;
        }

        foreach (['about', 'privacy'] as $slug) {
            // Prompt to restore static page if its missing
            if (
                null === QubitStaticPage::getBySlug($slug)
                && $this->askConfirmation(
                    [
                        sprintf('The %s page has been deleted. Do you wish to recreate it? (y/N)', $slug),
                    ],
                    'QUESTION_LARGE',
                    false
                )
            ) {
                $page = new QubitStaticPage();
                $page->slug = $slug;
                $page->sourceCulture = 'en';
                $page->save();
            }
        }
    }

    /**
     * If "check mode" is off then, for all standard static pages, reset
     * translations in the database to match the current AtoM default values.
     *
     * If "check mode" is on then, rather than resetting translations in the
     * database, return an array noting which static pages, indicated by slug,
     * have been changed.
     *
     * @param bool $checkMode True if to execute in "check mode"
     *
     * @return array array with changed slugs as key
     */
    protected function resetOrCheckForChangedStaticPages($checkMode = false)
    {
        $changes = [];

        foreach ($this->parseStaticPageFixtureData() as $slug => $translationData) {
            $page = QubitStaticPage::getBySlug($slug);

            // Only check if page still exists in the database (the privacy page can be deleted)
            if (!isset($page)) {
                continue;
            }

            // Check each standard translation against the database version of it
            foreach ($translationData as $culture => $translations) {
                // Check page title
                if ($translations['title'] != $page->getTitle(['culture' => $culture])) {
                    if (!$checkMode) {
                        $page->setTitle($translations['title'], ['culture' => $culture]);
                    }

                    $changes[$slug] = true;
                }

                // Check page content
                if ($translations['content'] != $page->getContent(['culture' => $culture])) {
                    if (!$checkMode) {
                        $page->setContent($translations['content'], ['culture' => $culture]);
                    }

                    $changes[$slug] = true;
                }
            }

            if (!empty($changes[$slug])) {
                $page->save();
            }
        }

        return $changes;
    }

    /*
     * Parse static page fixture data into a simplified form that's easier
     * to work with.
     *
     * Example of the structure:
     *
     *  [
     *    'pageslug' => [
     *      'en' => [
     *        'title' => 'English Title',
     *        'content' => 'English content'
     *      ],
     *      'fr' => ]
     *        'title' => 'Titre Français',
     *        'content' => 'Contenu français'
     *      ]
     *    ]
     *  ]
     *
     * @return array  simplified version of static page fixture data for each slug
     */
    protected function parseStaticPageFixtureData()
    {
        // Get data from static page fixture YAML
        $staticPageData = sfYaml::load('data/fixtures/staticPages.yml');

        // Parse into simplified data
        $simplified = [];

        foreach ($staticPageData['QubitStaticPage'] as $definition) {
            $slug = $definition['slug'];

            // Initialize data for slug, if need be
            if (empty($simplified[$slug])) {
                $simplified[$slug] = [];
            }

            // Add current culture's title and content to simplified data
            foreach ($definition['title'] as $culture => $title) {
                $simplified[$slug][$culture] = ['title' => $title];
            }

            foreach ($definition['content'] as $culture => $content) {
                $simplified[$slug][$culture]['content'] = $content;
            }
        }

        return $simplified;
    }

    /**
     * Figure out what's the last milestone used.
     *
     * @return int Previous milestone (e.g. 1, 2)
     */
    protected function getPreviousMilestone()
    {
        // There is no doubt that the user is running 1.x if the initial database
        // version was 92 or lower (before the fork happened)
        if ($this->initialVersion <= 92) {
            $previousMilestone = 1;
        }
        // Otherwise, we'll look for the milestone in the database
        else {
            $sql = 'SELECT value
                FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
                WHERE name = "milestone";';

            $previousMilestone = QubitPdo::fetchColumn($sql);
        }

        return $previousMilestone;
    }

    /**
     * Update the settings with the latest database version.
     *
     * @param int New database version
     * @param mixed $version
     */
    protected function updateDatabaseVersion($version)
    {
        $sql = 'UPDATE setting_i18n SET value = ? WHERE id = (SELECT id FROM setting WHERE name = ?);';
        QubitPdo::modify($sql, [$version, 'version']);
    }

    /**
     * Update the settings with the latest milestone.
     */
    protected function updateMilestone()
    {
        // Get current codebase milestone
        $substrings = preg_split('/\./', qubitConfiguration::VERSION);
        $milestone = array_shift($substrings);

        // Run SQL query
        $sql = 'UPDATE setting_i18n SET value = ? WHERE id = (SELECT id FROM setting WHERE name = ?);';
        QubitPdo::modify($sql, [$milestone, 'milestone']);
    }

    protected function parseDsn($dsn)
    {
        $params = [
            'host' => 'localhost',
            'port' => '3307',
        ];

        // Require a prefix
        if (!preg_match('/^(\w+):/', $dsn, $matches)) {
            return;
        }
        $params['prefix'] = $matches[1];

        // Require a dbname
        if (!preg_match('/dbname=(\w+)/', $dsn, $matches)) {
            return;
        }
        $params['dbname'] = $matches[1];

        // Optional params (host, port)
        if (preg_match('/host=([^;]+)/', $dsn, $matches)) {
            $params['host'] = $matches[1];
        }

        if (preg_match('/port=(\d+)/', $dsn, $matches)) {
            $params['port'] = $matches[1];
        }

        return $params;
    }

    /**
     * Run specific migrations by number.
     *
     * @param array  Array of migration numbers
     * @param mixed $numbers
     */
    private function runSpecificMigrations($numbers)
    {
        foreach ($numbers as $number) {
            if (is_numeric($number)) {
                $className = sprintf('arMigration%04d', $number);

                if (!class_exists($className)) {
                    $this->logSection('upgrade-sql', sprintf('Migration %d not found', $number));

                    continue;
                }

                $this->logSection('upgrade-sql', sprintf('Running migration %d', $number));

                $class = new $className();

                try {
                    if (true !== $class->up($this->configuration)) {
                        throw new sfException(sprintf('Failed to apply upgrade %s', get_class($class)));
                    }
                } catch (Exception $e) {
                    $this->logSection('upgrade-sql', sprintf('The task failed while trying to apply migration %d', $number));

                    throw $e;
                }
            }
        }
    }

    /**
     * Run new style migrations in a directory.
     *
     * @param int    $version             Version of schema before running migrations in this directory
     * @param string $migrationsDirectory Directory with migrations in it
     * @param int    $previousMilestone   Previous milestone
     * @param int    $currentMilestone    Current milestone
     * @param bool   $verboseMode         Verbose mode setting
     *
     * @return int Version of schema after running migrations in this directory
     */
    private function runMigrationsInDirectory($version, $migrationsDirectory, $previousMilestone, $currentMilestone, $verboseMode)
    {
        foreach (
            sfFinder::type('file')
                ->maxdepth(0)
                ->sort_by_name()
                ->name('arMigration*.class.php')
                ->in($migrationsDirectory) as $filename
        ) {
            // Initialize migration class
            $className = preg_replace('/.*(arMigration\d+).*/', '$1', $filename);
            $class = new $className();

            // This upgrade should have been applied already
            if ($class::VERSION <= $version) {
                // Unless the user is moving from 1.x to 2.x
                if (2 == $class::MIN_MILESTONE && 1 == $previousMilestone && 2 == $currentMilestone) {
                    // Run migration but don't bump dbversion
                    if (true !== $class->up($this->configuration)) {
                        throw new sfException('Failed to apply upgrade '.get_class($class));
                    }
                }
            }
            // New upgrades, not applied yet
            else {
                // Apply unless we are deadling with a 1.x user staying in 1.x
                if (1 != $previousMilestone || 1 != $currentMilestone) {
                    if ($verboseMode) {
                        $this->logSection('upgrade-sql', sprintf('Applying %s', $className));
                    }
                    // Run migration
                    if (true !== $class->up($this->configuration)) {
                        throw new sfException('Failed to apply upgrade '.get_class($class));
                    }

                    $this->updateDatabaseVersion(++$version);
                }
            }
        }

        return $version;
    }

    /**
     * Get the plugin settings and store it for the class instance.
     */
    private function getPluginSettings()
    {
        $this->pluginsSetting = QubitSetting::getByNameAndScope('plugins', null);
        if (null === $this->pluginsSetting) {
            throw new sfException('Could not get plugin settings from the database.');
        }
    }

    /**
     * Detect any plugins configured in the settings that aren't present
     * in plugins/, and remove them from the database.
     */
    private function removeMissingPluginsFromSettings()
    {
        $configuredPlugins = unserialize($this->pluginsSetting->getValue(['sourceCulture' => true]));
        $pluginsPresent = $this->getPluginsPresent();

        foreach ($configuredPlugins as $configPlugin) {
            if (!array_key_exists($configPlugin, $pluginsPresent)) {
                if (($key = array_search($configPlugin, $configuredPlugins)) !== false) {
                    // Confirmation
                    $question = "Plugin {$configPlugin} no longer exists. Remove it (Y/n)?";
                    if (!$options['no-confirmation'] && !$this->askConfirmation([$question], 'QUESTION_LARGE', true)) {
                        continue;
                    }

                    unset($configuredPlugins[$key]);
                    $this->logSection('upgrade-sql', "Removing plugin from settings: {$configPlugin}");
                }
            }
        }

        $this->pluginsSetting->setValue(serialize($configuredPlugins), ['sourceCulture' => true]);
        $this->pluginsSetting->save();
    }

    /**
     * Check if a theme configured in setting/setting_i18n isn't present under plugins/
     * If it isn't, prompt the user to choose one of the available themes detected in plugins/.
     */
    private function checkMissingThemes()
    {
        $presentThemes = $this->getPluginsPresent(true);
        $configuredPlugins = unserialize($this->pluginsSetting->getValue(['sourceCulture' => true]));

        // Check to see if any of the present themes in plugins/ are configured
        // to be used in the AtoM settings. If not, we'll prompt for a new theme.
        $themeMissing = true;

        foreach ($presentThemes as $presentThemeName => $presentThemePath) {
            if (in_array($presentThemeName, $configuredPlugins)) {
                // Valid theme configured + present in plugins/
                $themeMissing = false;

                break;
            }
        }

        if ($themeMissing) {
            $this->logSection('upgrade-sql', 'There is not a valid theme set currently.');

            // Confirmation
            $question = 'Would you like to choose a new theme (Y/n)?';
            $shouldConfirm = function_exists('readline') && !$options['no-confirmation'];
            if ($shouldConfirm && !$this->askConfirmation([$question], 'QUESTION_LARGE', true)) {
                return;
            }

            $chosenTheme = $this->getNewTheme($presentThemes);
            $configuredPlugins[] = $chosenTheme;

            $this->pluginsSetting->setValue(serialize($configuredPlugins), ['sourceCulture' => true]);
            $this->pluginsSetting->save();

            $this->logSection('upgrade-sql', "AtoM theme changed to {$chosenTheme}.");
        }
    }

    /**
     * Change to a new theme, selected out of a list provided.
     *
     * @param array $themes The themes present in the plugins/ folder ($name => $path)
     *
     * @return string The new theme name
     */
    private function getNewTheme($themes)
    {
        if (!function_exists('readline')) {
            throw new Exception('This task needs the PHP readline extension.');
        }

        while (true) {
            $this->logSection('upgrade-sql', 'Please enter a new theme choice:');

            $n = 0;
            foreach (array_keys($themes) as $theme) {
                echo ++$n.") {$theme}\n";
            }

            $choice = (int) readline('Select theme number: ');

            if ($choice >= 1 && $choice <= count($themes)) {
                $themeNames = array_keys($themes);

                return $themeNames[$choice - 1];
            }
        }
    }

    /**
     * Get plugins that are currently present in the plugins/ directory.
     *
     * @param $themePluginsOnly Whether to get all plugins or just theme plugins
     *
     * @return array An array containing the theme names and paths ($name => $path)
     */
    private function getPluginsPresent($themePluginsOnly = false)
    {
        $pluginPaths = $this->configuration->getAllPluginPaths();

        $plugins = [];
        foreach ($pluginPaths as $name => $path) {
            $className = $name.'Configuration';

            if (
                0 === strpos($path, sfConfig::get('sf_plugins_dir'))
                && is_readable($classPath = $path.'/config/'.$className.'.class.php')
            ) {
                if ($themePluginsOnly) {
                    require_once $classPath;
                    $class = new $className($this->configuration);

                    if (isset($class::$summary) && 1 === preg_match('/theme/i', $class::$summary)) {
                        $plugins[$name] = $path;
                    }
                } else {
                    $plugins[$name] = $path;
                }
            }
        }

        return $plugins;
    }
}
