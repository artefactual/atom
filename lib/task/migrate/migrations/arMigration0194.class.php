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
 * Add new UI labels for clipboard counts.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0194
{
    public const VERSION = 194;
    public const MIN_MILESTONE = 2;

    /**
     * Upgrade.
     *
     * @param mixed $configuration
     *
     * @return bool True if the upgrade succeeded, False otherwise
     */
    public function up($configuration)
    {
        $setting = new QubitSetting();
        $setting->name = 'descriptioncount';
        $setting->scope = 'ui_label';
        $setting->editable = 1;
        $setting->deleteable = 0;
        $setting->source_culture = 'en';
        $setting->setValue('Archival description count:&nbsp;', ['culture' => 'en']);
        $setting->save();

        $setting = new QubitSetting();
        $setting->name = 'authorityrecordcount';
        $setting->scope = 'ui_label';
        $setting->editable = 1;
        $setting->deleteable = 0;
        $setting->source_culture = 'en';
        $setting->setValue('Authority record count:&nbsp;', ['culture' => 'en']);
        $setting->save();

        $setting = new QubitSetting();
        $setting->name = 'repositorycount';
        $setting->scope = 'ui_label';
        $setting->editable = 1;
        $setting->deleteable = 0;
        $setting->source_culture = 'en';
        $setting->setValue('Archival institution count:&nbsp;', ['culture' => 'en']);
        $setting->save();

        return true;
    }
}
