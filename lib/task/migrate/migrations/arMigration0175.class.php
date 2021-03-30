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
 * Re-hash stored SHA-1 hash (and salt) using Argon2 to improve password security.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0175
{
    public const VERSION = 175;
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
        // Rename password hash column
        $sql = 'ALTER TABLE `user` CHANGE COLUMN `sha1_password` `password_hash` VARCHAR(255) DEFAULT NULL';
        QubitPdo::modify($sql);

        // Cycle through each user and re-hash stored SHA-1 hash (and salt)
        foreach (QubitUser::getAll() as $user) {
            $user->passwordHash = QubitUser::generatePasswordHash($user->passwordHash);
            $user->save();
        }

        return true;
    }
}
