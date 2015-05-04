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

class SitemapInformationObjectSet extends AbstractSitemapObjectSet
{
  public function init()
  {
    $query = <<<EOF
SELECT IO.level_of_description_id, IO.parent_id, S.slug, O.created_at, O.updated_at
FROM information_object IO
LEFT JOIN object O ON (IO.id = O.id)
LEFT JOIN slug S ON (IO.id = S.object_id)
LEFT JOIN status SS ON (IO.id = SS.object_id)
WHERE SS.status_id = ? AND IO.id != ?
EOF;

    $this->rec = $this->conn->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
    $this->rec->setFetchMode(PDO::FETCH_INTO, new SitemapInformationObjectUrl($this->config));
    $this->rec->execute(array(
      QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID,
      QubitInformationObject::ROOT_ID));
  }
}

class SitemapInformationObjectUrl extends AbstractSitemapUrl
{
  /**
   * A map of (int)levelOfDescriptionId => (string)priority
   */
  public static $priorities = array();

  public function __construct(&$config)
  {
    parent::__construct();

    if (!isset($config['information_object_priorities']))
    {
      return;
    }

    foreach ($config['information_object_priorities'] as $item)
    {
      $level = @$item[0]['level'];
      $priority = @$item[1]['priority'];

      if (is_null($level) || is_null($priority))
      {
        continue;
      }

      $criteria = new Criteria;
      $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::LEVEL_OF_DESCRIPTION_ID);
      $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
      $criteria->add(QubitTermI18n::NAME, $level);
      if (null !== $term = QubitTerm::getOne($criteria))
      {
        self::$priorities[(int)$term->id] = $priority;
      }
    }
  }

  protected function getPriority()
  {
    if (isset(self::$priorities[$this->level_of_description_id]))
    {
      return self::$priorities[$this->level_of_description_id];
    }

    // We don't recognize the level of description but we know that it's a collection root
    if (!empty($this->parent_id) && $this->parent_id == QubitInformationObject::ROOT_ID)
    {
      return "0.9";
    }

    return;
  }
}
