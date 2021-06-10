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

class sfPluginAdminPluginPluginsAction extends sfAction
{
    public function execute($request)
    {
        $this->form = new sfForm();
        $this->defaultTemplate = sfConfig::get('app_default_template_informationobject');

        if (!$this->context->user->isAdministrator()) {
            QubitAcl::forwardUnauthorized();
        }

        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, 'plugins');
        if (1 == count($query = QubitSetting::get($criteria))) {
            $setting = $query[0];

            $this->form->setDefault('enabled', unserialize($setting->getValue(['sourceCulture' => true])));
        }

        $configuration = ProjectConfiguration::getActive();
        $pluginPaths = $configuration->getAllPluginPaths();
        foreach (sfPluginAdminPluginConfiguration::$pluginNames as $name) {
            unset($pluginPaths[$name]);
        }

        $this->plugins = [];
        foreach ($pluginPaths as $name => $path) {
            $className = $name.'Configuration';
            if (sfConfig::get('sf_plugins_dir') == substr($path, 0, strlen(sfConfig::get('sf_plugins_dir'))) && is_readable($classPath = $path.'/config/'.$className.'.class.php')) {
                $this->installPluginAssets($name, $path);

                require_once $classPath;

                $class = new $className($configuration);

                // Build a list of plugins
                if (isset($class::$summary) && 0 === preg_match('/theme/i', $class::$summary)) {
                    $this->plugins[$name] = $class;
                }
            }
        }

        $this->form->setValidator('enabled', new sfValidatorChoice([
            'choices' => array_keys($this->plugins),
            'empty_value' => [],
            'multiple' => true,
        ]));

        if ($request->isMethod('post')) {
            $this->form->bind($request->getPostParameters());

            if ($this->form->isValid()) {
                if (1 != count($query)) {
                    $setting = new QubitSetting();
                    $setting->name = 'plugins';
                }

                $settings = unserialize($setting->getValue(['sourceCulture' => true]));

                $swordEnabled = in_array('qtSwordPlugin', $settings);

                foreach (array_keys($this->plugins) as $item) {
                    if (in_array($item, (array) $this->form->getValue('enabled'))) {
                        $settings[] = $item;
                    } elseif (false !== $key = array_search($item, $settings)) {
                        // Don't disable default plugins
                        if (
                            !('sfIsdiahPlugin' == $item
                            || 'sfIsaarPlugin' == $item
                            || ('sfIsadPlugin' == $item && 'isad' == $this->defaultTemplate)
                            || ('sfRadPlugin' == $item && 'rad' == $this->defaultTemplate)
                            || ('sfDcPlugin' == $item && 'dc' == $this->defaultTemplate)
                            || ('sfModsPlugin' == $item && 'mods' == $this->defaultTemplate))
                        ) {
                            unset($settings[$key]);
                        }
                    }
                }

                $setting->setValue(serialize(array_unique($settings)), ['sourceCulture' => true]);
                $setting->save();

                QubitCache::getInstance()->removePattern('settings:i18n:*');

                // Clear cache
                $cacheClear = new sfCacheClearTask(sfContext::getInstance()->getEventDispatcher(), new sfFormatter());
                $cacheClear->run();

                // Notify use if SWORD setting has been changed
                if ($swordEnabled != in_array('qtSwordPlugin', $settings)) {
                    $message = $this->context->i18n->__('SWORD plugin setting changed: the AtoM worker must be restarted for this change to take effect.');
                    $this->getUser()->setFlash('info', $message);
                }

                $this->redirect(['module' => 'sfPluginAdminPlugin', 'action' => 'plugins']);
            }
        }
    }

    // Copied from sfPluginPublishAssetsTask
    protected function installPluginAssets($name, $path)
    {
        $webDir = $path.'/web';

        if (is_dir($webDir)) {
            $filesystem = new sfFilesystem();
            $filesystem->relativeSymlink($webDir, sfConfig::get('sf_web_dir').'/'.$name, true);
        }
    }
}
