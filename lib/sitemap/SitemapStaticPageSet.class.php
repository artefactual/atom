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

class SitemapStaticPageSet extends AbstractSitemapObjectSet
{
  public function init()
  {
    $query = <<<EOF
SELECT S.slug, O.created_at, O.updated_at
FROM static_page ST
LEFT JOIN object O ON (ST.id = O.id)
LEFT JOIN slug S ON (ST.id = S.object_id)
EOF;

    $this->rec = $this->conn->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $this->rec->setFetchMode(PDO::FETCH_INTO, new SitemapStaticPageUrl);
    $this->rec->execute();
  }
}

class SitemapStaticPageUrl extends AbstractSitemapUrl
{
  public function getPriority()
  {
    return '1.0';
  }
}
