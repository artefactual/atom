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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM). If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

namespace Atom\Framework\Routing;

final class PropelResourceRepository implements ResourceRepository
{
    public function findBySlug(string $slug): ?object
    {
        $criteria = new \Criteria();
        $criteria->add(\QubitSlug::SLUG, $slug);
        $criteria->addJoin(\QubitSlug::OBJECT_ID, \QubitObject::ID);
        $resource = \QubitObject::get($criteria)->__get(0);

        return is_object($resource) ? $resource : null;
    }

    public function defaultTemplate(string $module): false|string
    {
        $sql = 'SELECT value
            FROM setting JOIN setting_i18n ON setting.id = setting_i18n.id
            WHERE scope = "default_template" AND name = ?';

        return \QubitPdo::fetchColumn($sql, [$module]);
    }

    public function informationObjectTemplate(object $resource): false|string
    {
        $sql = 'SELECT code
            FROM information_object JOIN term
                ON information_object.display_standard_id = term.id
            WHERE information_object.id = ? AND taxonomy_id = ?';

        return \QubitPdo::fetchColumn($sql, [
            $resource->id,
            \QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID,
        ]);
    }
}
