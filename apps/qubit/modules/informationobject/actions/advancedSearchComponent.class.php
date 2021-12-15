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
 * Information object advanced search component.
 */
class InformationObjectAdvancedSearchComponent extends sfComponent
{
    public function execute($request)
    {
        // Default to hiding the advanced search panel
        $this->showAdvanced = false;
        if (filter_var($request->showAdvanced, FILTER_VALIDATE_BOOLEAN)) {
            $this->showAdvanced = true;
        }

        // Default to inclusive date range type
        $this->rangeType = 'inclusive';
        if (isset($request->rangeType)) {
            $this->rangeType = $request->rangeType;
        }

        // Default to showing finding aid search fields
        $this->findingAidsEnabled = true;
        if ('1' !== sfConfig::get('app_findingAidsEnabled', '1')) {
            $this->findingAidsEnabled = false;
        }

        // Check "archival history" field visiblity
        $this->showArchivalHistory = false;
        if (
            (
                'rad' == $template
                && check_field_visibility(
                    'app_element_visibility_rad_archival_history'
                )
            ) || (
                'isad' == $template
                && check_field_visibility(
                    'app_element_visibility_isad_archival_history'
                )
            ) || (
                'isad' != $template && 'rad' != $template
            )
        ) {
            $this->showArchivalHistory = true;
        }

        // Check visible fields settings
        $this->showCopyright = sfConfig::get('app_toggleCopyrightFilter');
        $this->showMaterial = sfConfig::get('app_toggleMaterialFilter');

        // Use inclusive dates?
        $this->inclusiveDates = ('inclusive' == $this->rangeType);

        $this->fieldTypes = $this->getFieldTypes();
    }

    public function getFieldTypes()
    {
        sfProjectConfiguration::getActive()->loadHelpers(['I18N']);

        $fieldTypes = [
            '' => __('Any field'),
            'title' => __('Title'),
        ];

        if ($this->showArchivalHistory) {
            $fieldTypes['archivalHistory'] = __('Archival history');
        }

        $fieldTypes += [
            'scopeAndContent' => __('Scope and content'),
            'extentAndMedium' => __('Extent and medium'),
            'subject' => __('Subject access points'),
            'name' => __('Name access points'),
            'place' => __('Place access points'),
            'genre' => __('Genre access points'),
            'identifier' => __('Identifier'),
            'referenceCode' => __('Reference code'),
            'digitalObjectTranscript' => __('Digital object text'),
            'creator' => __('Creator'),
        ];

        if ($this->findingAidsEnabled) {
            $fieldTypes += [
                'findingAidTranscript' => __('Finding aid text'),
                'allExceptFindingAidTranscript' => __(
                    'Any field except finding aid text'
                ),
            ];
        }

        return $fieldTypes;
    }
}
