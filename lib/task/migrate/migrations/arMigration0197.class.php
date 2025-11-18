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
 * Add new setting for google analytics.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0197
{
    public const VERSION = 197;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add Header background colour settings.
        if (null === QubitSetting::getByName('google_analytics')) {
            $setting = new QubitSetting();
            $setting->name = 'google_analytics';
            $setting->editable = 1;
            $setting->value = '';
            $setting->save();
        }

        return true;
    }
}
