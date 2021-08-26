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

class StaticPageHomeAction extends StaticPageIndexAction
{
    public function execute($request)
    {
        parent::execute($request);

        if (null === $this->mostPopularLastMonth) {
            $this->mostPopularLastMonth = $this->getMostPopularLastMonth();
        }

        if (null === $this->carouselItems) {
            $this->carouselItems = sfYaml::load('plugins/arUnogPlugin/carousel/config.yml');
        }

        $culture = $this->context->user->getCulture();
        if (!in_array($culture, ['en', 'fr'])) {
            $culture = 'en';
        }
        $this->culture = $culture;
    }

    private function getMostPopularLastMonth()
    {
        $this->ribbonCollectionSlug = sfYaml::load('plugins/arUnogPlugin/ribbon/config.yml')['slug'];
        $io = QubitInformationObject::getBySlug($this->ribbonCollectionSlug);

        $sql = 'SELECT s.slug, d2.path, d2.name, COUNT(access_log.object_id) AS count';
        $sql .= ' FROM access_log';
        $sql .= ' JOIN digital_object AS d1 ON (access_log.object_id = d1.object_id)';
        $sql .= ' JOIN digital_object AS d2 ON (d1.id = d2.parent_id)';
        $sql .= ' JOIN slug AS s ON (access_log.object_id = s.object_id)';
        $sql .= ' JOIN information_object as i ON (i.id = access_log.object_id)';
        $sql .= ' JOIN status AS t ON (access_log.object_id = t.object_id)';
        $sql .= ' WHERE d2.usage_id = :usage';
        $sql .= '  AND access_log.access_date BETWEEN DATE_SUB(NOW(), INTERVAL 1 MONTH) AND NOW()';
        $sql .= '  AND i.lft > :lft AND i.lft < :rgt';
        $sql .= '  AND t.status_id = :published';
        $sql .= ' GROUP BY (access_log.object_id)';
        $sql .= ' ORDER BY count DESC';
        $sql .= ' LIMIT 16';

        $stmt = QubitPdo::prepare($sql);
        $stmt->execute([':lft' => $io->lft, ':rgt' => $io->rgt, ':published' => QubitTerm::PUBLICATION_STATUS_PUBLISHED_ID, ':usage' => QubitTerm::THUMBNAIL_ID]);

        return $stmt->fetchAll();
    }
}
