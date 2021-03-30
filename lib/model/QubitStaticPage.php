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

class QubitStaticPage extends BaseStaticPage
{
    public function __toString()
    {
        return (string) $this->title;
    }

    public function isProtected()
    {
        return 'home' == $this->slug;
    }

    protected function insert($connection = null)
    {
        if (!isset($this->slug)) {
            $this->slug = QubitSlug::slugify($this->__get('title', ['sourceCulture' => true]));
        }

        return parent::insert($connection);
    }

    protected function update($connection = null)
    {
        if (!isset($connection)) {
            $connection = Propel::getConnection();
        }

        $statement = $connection->prepare(
            'UPDATE '.QubitSlug::TABLE_NAME
            .' SET '.QubitSlug::SLUG.' = ?'
            .' WHERE '.QubitSlug::OBJECT_ID.' = ?'
        );

        if (1 > strlen($this->slug)) {
            $statement->execute([QubitSlug::random(), $this->id]);

            return;
        }

        try {
            $statement->execute([$this->slug, $this->id]);
        }

        // Collision? Try random, digit and letter slug
        catch (PDOException $e) {
            $statement->execute([QubitSlug::random(), $this->id]);
        }

        return parent::update($connection);
    }
}
