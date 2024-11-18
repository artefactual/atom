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
 * Digital Object coverflow component.
 *
 * @author     david juhasz <david@artefactual.com>
 */
class DigitalObjectImageflowComponent extends sfComponent
{
    public function execute($request)
    {
        if (!sfConfig::get('app_toggleIoSlider')) {
            return sfView::NONE;
        }

        $this->thumbnails = [];

        // Set limit (null for no limit)
        if (!isset($request->showFullImageflow) || 'true' != $request->showFullImageflow) {
            $this->limit = sfConfig::get('app_hits_per_page', 10);
        }

        // Add thumbs
        $criteria = new Criteria();
        $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::OBJECT_ID);

        $criteria->add(
            QubitInformationObject::LFT,
            $this->resource->lft,
            Criteria::GREATER_THAN
        );

        $criteria->add(
            QubitInformationObject::RGT,
            $this->resource->rgt,
            Criteria::LESS_THAN
        );

        if (isset($this->limit)) {
            $criteria->setLimit($this->limit);
        }

        // Hide drafts
        $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

        foreach (QubitDigitalObject::get($criteria) as $item) {
            if (QubitTerm::OFFLINE_ID == $item->usageId) {
                $thumbnail = QubitDigitalObject::getGenericRepresentation(
                    $item->mimeType,
                    QubitTerm::THUMBNAIL_ID
                );

                $thumbnail->setParent($item);
            } else {
                // Ensure the user has permissions to see a thumbnail
                if (!QubitAcl::check($item->object, 'readThumbnail')) {
                    $thumbnail = QubitDigitalObject::getGenericRepresentation(
                        $item->mimeType,
                        QubitTerm::THUMBNAIL_ID
                    );

                    $thumbnail->setParent($item);
                } else {
                    $thumbnail = $item->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID);

                    if (!$thumbnail) {
                        $thumbnail = QubitDigitalObject::getGenericRepresentation(
                            $item->mimeType,
                            QubitTerm::THUMBNAIL_ID
                        );

                        $thumbnail->setParent($item);
                    }
                }
            }

            $this->thumbnails[] = $thumbnail;
        }

        // Get total number of descendant digital objects
        $this->total = $this->getDescendantDigitalObjectCount();

        if (0 === count($this->thumbnails)) {
            return sfView::NONE;
        }
    }

    /**
     * Query Elasticsearch to get a count of all digital objects that are
     * descendants of the current resource.
     *
     * @return int count of descendants with digital objects
     */
    protected function getDescendantDigitalObjectCount()
    {
        // Set search "size" to zero, because we just need a count of results, not
        // the found record data
        $search = new arElasticSearchPluginQuery(0);
        $search->addAdvancedSearchFilters(
            InformationObjectBrowseAction::$NAMES,
            [
                'ancestor' => $this->resource->id,
                'topLod' => false,
                'onlyMedia' => true,
            ],
            'isad'
        );

        $results = QubitSearch::getInstance()
            ->index
            ->getIndex('QubitInformationObject')
            ->search($search->getQuery(false, true));

        return $results->getTotalHits();
    }
}
