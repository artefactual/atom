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

/*
 * Update access statements
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0136
{
  const
    VERSION = 136, // The new database version
    MIN_MILESTONE = 2; // The minimum milestone required

  public function up($configuration)
  {
    // Obtain the different translations of the previous settings
    $accessDisallowWarningI18nValues = $this->getSettingI18nValues('access_disallow_warning', 'ui_label');
    $accessConditionalWarningI18nValues = $this->getSettingI18nValues('access_conditional_warning', 'ui_label');

    // Populate new settings, there are going to be two statements per each basis
    // available in the RIGHTS_BASIS_ID taxonomy. By default, we populate alz
    // the statements like the used to be before, making sure that all the
    // translations are also migrated.
    foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::RIGHT_BASIS_ID) as $item)
    {
      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_disallow";
      $setting->scope = 'access_statement';
      foreach ($accessDisallowWarningI18nValues as $langCode => $value)
      {
        $setting->setValue($value, array('culture' => $langCode));
      }
      $setting->save();

      $setting = new QubitSetting;
      $setting->name = "{$item->slug}_conditional";
      $setting->scope = 'access_statement';
      foreach ($accessConditionalWarningI18nValues as $langCode => $value)
      {
        $setting->setValue($value, array('culture' => $langCode));
      }
      $setting->save();
    }

    // Delete UI labels access_disallow_warning and access_conditional_warning
    foreach (array('access_disallow_warning', 'access_conditional_warning') as $item)
    {
      $setting = QubitSetting::getByNameAndScope($item, 'ui_label');
      if (null !== $setting)
      {
        $setting->delete();
      }
    }

    return true;
  }

  /**
   * Build a dictionary with all the different translations for a given setting.
   * The translations are indexed in the dictionary with their language codes.
   */
  protected function getSettingI18nValues($name, $scope)
  {
    $values = array();
    $sql = "SELECT `setting_i18n`.`value`, `setting_i18n`.`culture` FROM `setting` LEFT JOIN `setting_i18n` ON (`setting`.`id` = `setting_i18n`.`id`) WHERE `setting`.`name` = ? AND `setting`.`scope` = ?;";
    foreach (QubitPdo::fetchAll($sql, array($name, $scope)) as $item)
    {
      if (empty($item->value))
      {
        continue;
      }

      $values[$item->culture] = $item->value;
    }

    return $values;
  }
}
