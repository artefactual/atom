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
 * Add new user interface label for actor relations.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0180
{
    public const VERSION = 180;
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
        if (null === QubitSetting::getByName('authority_record_relationships')) {
            $setting = new QubitSetting();
            $setting->name = 'authority_record_relationships';
            $setting->scope = 'ui_label';
            $setting->editable = 1;
            $setting->deleteable = 0;
            $setting->source_culture = 'en';
            $setting->setValue('Authority record relationships', ['culture' => 'en']);
            $setting->save();
        }

        return true;
    }
}
