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

class arSolrRepository extends arSolrModelBase
{
    public function load()
    {
        $criteria = new Criteria();
        $criteria->add(QubitRepository::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);
        $repositories = QubitRepository::get($criteria);

        $this->count = count($repositories);

        return $repositories;
    }

    public function populate()
    {
        $errors = [];

        $repositories = $this->load();

        foreach ($repositories as $key => $repository) {
            try {
                $data = self::serialize($repository);

                $this->search->addDocument($data, 'QubitRepository');

                $this->logEntry($repository->__toString(), $key + 1);
            } catch (sfException $e) {
                $errors[] = $e->getMessage();
            }
        }

        return $errors;
    }

    public static function serialize($object)
    {
        $serialized = [];

        $serialized['id'] = $object->id;
        $serialized['slug'] = $object->slug;
        $serialized['identifier'] = $object->identifier;

        // Related terms
        $relatedTerms = arSolrModelBase::getRelatedTerms(
            $object->id,
            [
                QubitTaxonomy::REPOSITORY_TYPE_ID,
                QubitTaxonomy::THEMATIC_AREA_ID,
                QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID,
            ]
        );

        if (isset($relatedTerms[QubitTaxonomy::REPOSITORY_TYPE_ID])) {
            $serialized['types'] = $relatedTerms[QubitTaxonomy::REPOSITORY_TYPE_ID];
        }

        if (isset($relatedTerms[QubitTaxonomy::THEMATIC_AREA_ID])) {
            $serialized['thematicAreas'] = $relatedTerms[QubitTaxonomy::THEMATIC_AREA_ID];
        }

        if (isset($relatedTerms[QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID])) {
            $serialized['geographicSubregions'] = $relatedTerms[QubitTaxonomy::GEOGRAPHIC_SUBREGION_ID];
        }

        foreach ($object->contactInformations as $contactInformation) {
            $serialized['contactInformations'][] = arSolrContactInformation::serialize($contactInformation);
        }

        $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$object->id, QubitTerm::OTHER_FORM_OF_NAME_ID]) as $item) {
            $serialized['otherNames'][] = arSolrOtherName::serialize($item);
        }

        $sql = 'SELECT id, source_culture FROM '.QubitOtherName::TABLE_NAME.' WHERE object_id = ? AND type_id = ?';
        foreach (QubitPdo::fetchAll($sql, [$object->id, QubitTerm::PARALLEL_FORM_OF_NAME_ID]) as $item) {
            $serialized['parallelNames'][] = arSolrOtherName::serialize($item);
        }

        if ($object->existsLogo()) {
            $serialized['logoPath'] = $object->getLogoPath();
        }

        $serialized['createdAt'] = arSolrPluginUtil::convertDate($object->createdAt);
        $serialized['updatedAt'] = arSolrPluginUtil::convertDate($object->updatedAt);

        $serialized['sourceCulture'] = $object->sourceCulture;
        $serialized['i18n'] = self::serializeI18ns($object->id, ['QubitActor', 'QubitRepository']);
        self::addExtraSortInfo($serialized['i18n'], $object);

        return $serialized;
    }

    public static function update($object)
    {
        $data = self::serialize($object);

        QubitSearch::getSolrInstance()->addDocument($data, 'QubitRepository');

        return true;
    }

    /**
     * We store extra I18n fields (city, region) for table sorting purposes in the repository browse page.
     *
     * These values will be the city & region of the primary contact if valid, otherwise the first contact
     * that has a valid city / region will be used.
     *
     * @param mixed $i18n
     * @param mixed $object
     */
    private static function addExtraSortInfo(&$i18n, $object)
    {
        foreach (sfConfig::get('app_i18n_languages') as $lang) {
            if ($object->getCity(['culture' => $lang])) {
                $i18n[$lang]['city'] = $object->getCity(['culture' => $lang]);
            }

            if ($object->getRegion(['culture' => $lang])) {
                $i18n[$lang]['region'] = $object->getRegion(['culture' => $lang]);
            }
        }
    }
}
