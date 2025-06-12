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
 * Add new setting for header colour customization.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0195
{
    public const VERSION = 195;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        // Add Header background colour settings.
        if (null === QubitSetting::getByName('header_background_colour')) {
            $setting = new QubitSetting();
            $setting->name = 'header_background_colour';
            $setting->editable = 1;
            $setting->value = '#212529';
            $setting->save();
        }

        return true;
    }
}
