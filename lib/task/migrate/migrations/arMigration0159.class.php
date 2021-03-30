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
 * Add new setting for permissive slug generation. DB column collation change.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0159
{
    public const VERSION = 159;
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
        if (null === QubitSetting::getByName('permissive_slug_creation')) {
            $setting = new QubitSetting();
            $setting->name = 'permissive_slug_creation';
            $setting->value = QubitSlug::SLUG_RESTRICTIVE;
            $setting->editable = 1;
            $setting->source_culture = 'en';
            $setting->setValue(QubitSlug::SLUG_RESTRICTIVE, ['culture' => 'en']);
            $setting->save();
        }

        $sql = 'ALTER TABLE `slug` MODIFY `slug` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_bin';
        QubitPdo::modify($sql);

        return true;
    }
}
