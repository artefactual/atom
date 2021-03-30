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
 * Digital Object video display component.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class DigitalObjectShowVideoComponent extends sfComponent
{
    /**
     * Show a representation of a digital object image.
     *
     * @param sfWebRequest $request
     */
    public function execute($request)
    {
        // Get representation by usage type
        $this->representation = $this->resource->getRepresentationByUsage($this->usageType);

        // If we can't find a representation for this object, try their parent
        if (!$this->representation && ($parent = $this->resource->parent)) {
            $this->representation = $parent->getRepresentationByUsage($this->usageType);
        }

        // Set up display of video in mediaelement
        if ($this->representation) {
            $this->response->addJavaScript('/vendor/mediaelement/mediaelement-and-player.min.js', 'last');
            $this->response->addJavaScript('mediaelement', 'last');
            $this->response->addStyleSheet('/vendor/mediaelement/mediaelementplayer.min.css');

            // If this is a reference movie, get the thumbnail representation for the
            // place holder image
            $this->showMediaPlayer = true;
            if (QubitTerm::REFERENCE_ID == $this->usageType) {
                $this->thumbnail = $this->resource->getRepresentationByUsage(QubitTerm::THUMBNAIL_ID);
            }

            list($this->width, $this->height) = QubitDigitalObject::getImageMaxDimensions($this->usageType);

            // For javascript_tag()
            if (QubitTerm::CHAPTERS_ID != $this->usageType && QubitTerm::SUBTITLES_ID != $this->usageType) {
                $this->representationFullPath = public_path($this->representation->getFullPath());
            }
        }
        // If representation is not a valid digital object, return a generic icon
        else {
            $this->showMediaPlayer = false;
            $this->representation = QubitDigitalObject::getGenericRepresentation($this->resource->mimeType, $this->usageType);
        }
    }
}
