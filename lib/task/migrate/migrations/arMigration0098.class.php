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
 * Include sub-item level "part" in the levels of description taxonomy
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0098
{
    public const VERSION = 98;
    public const MIN_MILESTONE = 1;

    /**
     * Upgrade.
     *
     * @param mixed $configuration
     *
     * @return bool True if the upgrade succeeded, False otherwise
     */
    public function up($configuration)
    {
        $defaultCulture = sfConfig::get('sf_default_culture', 'en');

        // Create root repository:
        // - Use raw SQL to avoid mismatches with the ORM in later versions.
        // - Ignore nested set values. At the point we're fixing this issue,
        //   the nested set will be removed from the actor table in arMigration0181.
        $sql = "INSERT INTO object (class_name, created_at, updated_at, id, serial_number)
            VALUES ('QubitRepository', now(), now(), ?, 0);";
        QubitPdo::modify($sql, [QubitRepository::ROOT_ID]);

        $sql = 'INSERT INTO actor (id, lft, rgt, source_culture) VALUES (?, 0, 0, ?);';
        QubitPdo::modify($sql, [QubitRepository::ROOT_ID, $defaultCulture]);

        $sql = 'INSERT INTO repository (id, source_culture) VALUES (?, ?);';
        QubitPdo::modify($sql, [QubitRepository::ROOT_ID, $defaultCulture]);

        // Obtain all repositories except the root
        $sql = sprintf(
            'SELECT t1.id
            FROM %s t1
            LEFT JOIN %s t2
            ON t1.id = t2.id
            WHERE class_name = ?
            AND t1.id != ?;',
            QubitActor::TABLE_NAME,
            QubitObject::TABLE_NAME
        );

        $rows = QubitPdo::fetchAll($sql, ['QubitRepository', QubitRepository::ROOT_ID]);

        // Add parent to all existing repositories
        foreach ($rows as $repository) {
            $sql = sprintf('UPDATE %s t1
                LEFT JOIN %s t2
                ON t1.id = t2.id
                SET parent_id = ?, lft = 0, rgt = 0
                WHERE t1.id = ?
                AND t1.id != ?;', QubitActor::TABLE_NAME, QubitObject::TABLE_NAME);

            QubitPdo::modify($sql, [
                QubitRepository::ROOT_ID,
                $repository->id,
                QubitRepository::ROOT_ID,
            ]);
        }

        // Add menu nodes for repository permissions
        if (null !== $parentNode = QubitMenu::getByName('groups')) {
            $menu = new QubitMenu();
            $menu->parentId = $parentNode->id;
            $menu->name = 'groupRepositoryAcl';
            $menu->path = 'aclGroup/indexRepositoryAcl?id=%currentId%';
            $menu->sourceCulture = 'en';
            $menu->label = 'Archival institution permissions';
            $menu->save();
        } else {
            $this->logSection('upgrade-sql', 'The group permissions menu node for repository could not be added.', null, 'ERROR');
        }

        if (null !== $parentNode = QubitMenu::getByName('users')) {
            $menu = new QubitMenu();
            $menu->parentId = $parentNode->id;
            $menu->name = 'userRepositoryAcl';
            $menu->path = 'user/indexRepositoryAcl?slug=%currentSlug%';
            $menu->sourceCulture = 'en';
            $menu->label = 'Archival institution permissions';
            $menu->save();
        } else {
            $this->logSection('upgrade-sql', 'The user permissions menu node for repository could not be added.', null, 'ERROR');
        }

        return true;
    }
}
