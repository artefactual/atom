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
 * Extend BaseSetting functionality.
 *
 * @author     MJ Suhonos
 * @author     Jack Bates <jack@nottheoilrig.com>
 */
class QubitSetting extends BaseSetting
{
    public static $premisAccessRightValueDefaults = [
        'allow_master' => 0,
        'allow_reference' => 0,
        'allow_thumb' => 0,
        'conditional_master' => 0,
        'conditional_reference' => 0,
        'conditional_thumb' => 0,
        'disallow_master' => 0,
        'disallow_reference' => 0,
        'disallow_thumb' => 0,
    ];
    // List of scopes with translatable settings,
    // QubitI18nConsolidatedExtract checks this array to add those
    // settings value from /data/fixtures/settings.yml to the XLIFF files
    public static $translatableScopes = ['ui_label'];

    public function __toString()
    {
        if (null == $value = $this->value) {
            $value = $this->getValue(['sourceCulture' => true]);
        }

        return (string) $value;
    }

    // wrapper convenience methods
    public function isEditable()
    {
        return $this->editable;
    }

    public function isDeleteable()
    {
        return $this->deleteable;
    }

    /**
     * Get all values from QubitSetting table, in appropriate culture, in
     * sfConfig array format.
     *
     * @return array name/value pairs formatted for addition to sfConfig array
     */
    public static function getSettingsArray()
    {
        $sql = 'SELECT
                setting.*,
                (CASE WHEN (current.VALUE IS NOT NULL AND current.VALUE <> "") THEN current.VALUE ELSE source.VALUE END) AS value,
                (CASE WHEN (current.CULTURE IS NOT NULL AND current.CULTURE <> "") THEN current.CULTURE ELSE source.CULTURE END) AS culture,
                source.VALUE AS value_source
            FROM '.QubitSetting::TABLE_NAME.'
            LEFT JOIN '.QubitSettingI18n::TABLE_NAME.' current
                ON (setting.ID = current.id AND current.CULTURE = ?)
            LEFT JOIN '.QubitSettingI18n::TABLE_NAME.' source
                ON (setting.ID = source.id AND source.CULTURE = setting.SOURCE_CULTURE)';

        $settings = $i18nLanguages = [];
        $culture = sfContext::getInstance()->user->getCulture();

        foreach (QubitPdo::fetchAll($sql, [$culture]) as $qubitSetting) {
            if ($qubitSetting->scope) {
                // Collect enabled languages into a single setting
                if ('i18n_languages' == $qubitSetting->scope) {
                    $i18nLanguages[] = $qubitSetting->value_source;

                    continue;
                }

                $key = 'app_'.$qubitSetting->scope.'_'.$qubitSetting->name;
            } else {
                $key = 'app_'.$qubitSetting->name;
            }

            $settings[$key] = $qubitSetting->value;

            $settings[$key.'__source'] = $qubitSetting->value_source;
        }

        $settings['app_i18n_languages'] = $i18nLanguages;

        return $settings;
    }

    public function getCulture(array $options = [])
    {
        // get culture based on i18n fallback criteria
        if ($settingI18n = QubitSettingI18n::getByIdAndCulture($this->id, sfContext::getInstance()->user->getCulture())) {
            return $settingI18n->getCulture();
        }
    }

    /**
     * Return the source culture value for this setting, when current context is
     * not the source culture.  This is used on edit forms to show the source
     * culture value for a field as an aid for tranlslation.
     *
     * @param string $culture current culture context
     *
     * @return string source culture value for field
     */
    public function getSourceCultureHelper($culture)
    {
        if (strlen($sourceCultureValue = $this->getValue(['sourceCulture' => true])) > 0 && $culture != $this->getSourceCulture()) {
            return $sourceCultureValue;
        }

        return null;
    }

    /**
     * Get all system settings that are "non-global" (scope <> NULL) and not
     * "site information" settings.
     *
     * @return QubitQuery collection of QubitSetting objects
     */
    public static function getLocalSettings()
    {
        $criteria = new Criteria();
        $criteria->add(QubitSetting::SCOPE, null, Criteria::ISNOTNULL);
        $criteria->add(QubitSetting::SCOPE, 'site_information', Criteria::NOT_EQUAL);

        return QubitSetting::get($criteria);
    }

    /**
     * Get all settings matching $scope parameter.
     *
     * @param string $scope
     */
    public static function getByScope($scope = null)
    {
        $criteria = new Criteria();
        if (null !== $scope) {
            $criteria->add(QubitSetting::SCOPE, $scope);
        } else {
            $criteria->add(QubitSetting::SCOPE, null, Criteria::ISNULL);
        }

        return QubitSetting::get($criteria);
    }

    /**
     * Get a setting by it's name.
     *
     * @param mixed $name
     *
     * @return QubitSetting object
     */
    public static function getByName($name)
    {
        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, $name);

        return QubitSetting::getOne($criteria);
    }

    /**
     * Get a setting by it's name & scope.
     *
     * @param mixed $name
     * @param mixed $scope
     *
     * @return QubitSetting object
     */
    public static function getByNameAndScope($name, $scope)
    {
        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, $name);
        $criteria->add(QubitSetting::SCOPE, $scope);

        return QubitSetting::getOne($criteria);
    }

    /**
     * Find a setting, and save a new value to it.
     *
     * @param mixed $name
     * @param mixed $value
     * @param mixed $options
     *
     * @return QubitSetting object
     */
    public static function findAndSave($name, $value, $options)
    {
        // Search for existing setting by name (optionally, scope)
        $criteria = new Criteria();
        $criteria->add(QubitSetting::NAME, $name);

        if (isset($options['scope'])) {
            $criteria->add(QubitSetting::SCOPE, $options['scope']);
        }

        $setting = QubitSetting::getOne($criteria);

        // If setting doesn't already exist, create a new one when
        // $options['createNew'] is true
        if (!isset($setting) && true == $options['createNew']) {
            unset($options['createNew']);

            $setting = new QubitSetting();
            $setting->setName($name);
            $setting->setEditable(true); // Default to true

            // If $options['scope'] is different than QubitSetting->scope a new
            // QubitSetting is created, so this is only necessary here
            if (isset($options['scope'])) {
                $setting->setScope($options['scope']);
                unset($options['scope']);
            }
        }

        if (!isset($setting)) {
            return;
        }

        // Set values and save setting
        if (isset($options['editable'])) {
            $setting->setEditable($options['editable']);
            unset($options['editable']);
        }

        $setting->setValue($value, $options);
        $setting->save();

        return $setting;
    }

    /**
     * Create a new setting object with some default properties.
     *
     * @param string $name    object name
     * @param string $value   object value
     * @param array  $options array of options
     */
    public static function createNewSetting($name, $value, $options = [])
    {
        $setting = new QubitSetting();
        $setting->setName($name);
        $setting->setValue($value);

        if (isset($options['scope'])) {
            $setting->setScope($options['scope']);
        }

        // Default "editable" to true, unless forced to false
        $setting->setEditable(1);
        if (isset($options['editable']) && false == $options['editable']) {
            $setting->setEditable(0);
        }

        // Default "deleteable" to true, unless forced to false
        $setting->setDeleteable(1);
        if (isset($options['deleteable']) && false == $options['deleteable']) {
            $setting->setDeleteable(0);
        }

        // Set the source culture option
        if (isset($options['sourceCulture'])) {
            $setting->setSourceCulture($options['sourceCulture']);
        }

        // Set the culture option
        if (isset($options['culture'])) {
            $setting->setCulture($options['culture']);
        }

        return $setting;
    }
}
