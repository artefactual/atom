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
 * Add accession label.
 */
class arMigration0196
{
    public const VERSION = 196;
    public const MIN_MILESTONE = 2;

    public function up($configuration)
    {
        if (null === QubitSetting::getByName('accession')) {
            $setting = new QubitSetting();
            $setting->name = 'accession';
            $setting->scope = 'ui_label';
            $setting->editable = 1;
            $setting->deleteable = 0;
            $setting->source_culture = 'en';
            $setting->setValue('Accession', ['culture' => 'en']);
            $setting->save();
        }

        if (null === QubitSetting::getByName('accessioncount')) {
            $setting = new QubitSetting();
            $setting->name = 'accessioncount';
            $setting->scope = 'ui_label';
            $setting->editable = 1;
            $setting->deleteable = 0;
            $setting->source_culture = 'en';
            $setting->setValue('Accession count:&nbsp;', ['culture' => 'en']);
            $setting->save();
        }

        return true;
    }
}
