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
 * @author     David Juhasz <david@artefactual.com>
 */
class sfEadPlugin
{
    public $resource;
    public $siteBaseUrl;

    public static $ENCODING_MAP = [
        'isad' => [
            'relatedencoding' => 'ISAD(G)v2',
            'eadid' => 'identifier',
            'titleproper' => 'title',
            'author' => 'creator',
            'publisher' => 'publisher',
            'date' => 'date',
            'language' => 'language',
            'languageOfDescription' => 'languageOfDescription',
            'script' => 'script',
            'scriptOfDescription' => 'scriptOfDescription',
            'materialType' => '1.1C',
            'descrules' => '3.7.2',
            'scopecontent' => '3.3.1',
            'arrangement' => '3.3.4',
            'phystech' => '3.4.3',
            'appraisal' => '3.3.2',
            'acqinfo' => '3.2.4',
            'accruals' => '3.3.3',
            'custodhist' => '3.2.3',
            'originalsloc' => '3.5.1',
            'altformavail' => '3.5.2',
            'relatedmaterial' => '3.5.3',
            'accessrestrict' => '3.4.1',
            'userestrict' => '3.4.2',
            'otherfindaid' => '3.4.5',
            'bibliography' => '3.5.4',
            'unittitle' => '3.1.2',
            'unitid' => '3.1.1',
            'unitdateDefault' => '3.1.3',
            'extent' => '3.1.5',
            'langmaterial' => '3.4.3',
            'note' => '3.6.1',
            'bioghist' => '3.2.2',
            'origination' => '3.2.1',
        ],
        'rad' => [
            'relatedencoding' => 'RAD',
            'eadid' => 'identifier',
            'titleproper' => 'title',
            'author' => 'creator',
            'publisher' => 'publisher',
            'date' => 'date',
            'language' => 'language',
            'languageOfDescription' => 'languageOfDescription',
            'script' => 'script',
            'scriptOfDescription' => 'scriptOfDescription',
            'scopecontent' => '1.7D',
            'arrangement' => '1.8B13',
            'phystech' => '1.8B9a',
            'acqinfo' => '1.8B12',
            'accruals' => '1.8B19',
            'custodhist' => '1.7C',
            'originalsloc' => '1.8B15a',
            'altformavail' => '1.8B15b',
            'relatedmaterial' => '1.8B18',
            'accessrestrict' => '1.8B16a',
            'userestrict' => '1.8B16c',
            'otherfindaid' => '1.8B17',
            'unittitle' => '1.1B',
            'unitid' => '1.8B11',
            'unitdate' => '1.4B2',
            'unitdateDefault' => '1.4F',
            'unitdatemanufacturing' => '1.4G',
            'extent' => '1.5B1',
            'langmaterial' => '1.8B9a',
            'note' => '1.8B21',
            'bioghist' => '1.7B',
            'origination' => '1.4D',
            'genreform' => '1.1C',
            'parallel' => '1.1D',
            'otherinfo' => '1.1E',
            'statrep' => '1.1F',
            'titleVariation' => '1.8B1',
            'titleAttributions' => '1.8B6',
            'titleContinuation' => '1.8B4',
            'titleStatRep' => '1.8B5',
            'titleParallel' => '1.8B3',
            'titleSource' => '1.8B2',
            'editionstatement' => '1.2B1',
            'statementofresp' => '1.2C',
            'cartographic' => '5.3B1',
            'projection' => '5.3C1',
            'coordinates' => '5.3D',
            'architectural' => '6.3B',
            'philatelic' => '12.3B1',
            'titleProperOfPublishersSeries' => '1.6B1',
            'parallelTitleOfPublishersSeries' => '1.6C1',
            'otherTitleInformationOfPublishersSeries' => '1.6D1',
            'statementOfResponsibilityRelatingToPublishersSeries' => '1.6E1',
            'numberingWithinPublishersSeries' => '1.6F',
            'standardNumber' => '1.9B1',
            'bibSeries' => '1.8B10',
            'edition' => '1.8B7',
            'physDesc' => '1.8B9',
            'conservation' => '1.8B9b',
            'material' => '1.5E',
            'alphanumericDesignation' => '1.8B11',
            'rights' => '1.8B16b',
            'generalNote' => '1.8B21',
            'nameDefault' => '1.4D',
            'nameManufacturer' => '1.4G',
            'geogDefault' => '1.4C',
            'geogManufacturer' => '1.4G',
        ],
    ];

    public function __construct($resource)
    {
        $this->resource = $resource;

        $this->version = 'Access to Memory (AtoM) '.qubitConfiguration::VERSION;

        $this->siteBaseUrl = QubitSetting::getByName('siteBaseUrl');
        $this->siteBaseUrl = (null == $this->siteBaseUrl) ? '' : $this->siteBaseUrl;
    }

    public function __get($name)
    {
        return $this->resource->{$name};
    }

    public function subjectHasNonBlankSourceNotes(&$subject)
    {
        $hasNonBlankNotes = false;

        $notes = $subject->getTerm()->getSourceNotes();
        foreach ($notes as $note) {
            if ('' != $note) {
                $hasNonBlankNotes = true;
            }
        }

        return $hasNonBlankNotes;
    }

    public function getAssetPath($do, $getReference = false)
    {
        if ($getReference) {
            $do = $do->reference;
        }

        if ($this->siteBaseUrl) {
            return $this->siteBaseUrl.'/'.ltrim($do->getFullPath(), '/');
        }

        return $this->siteBaseUrl.'/'.$do->getFullPath();
    }

    public function renderEadId()
    {
        $countryCode = $mainAgencyCode = '';

        if (null !== $this->resource->getRepository(['inherit' => true])) {
            if (null !== $country = $this->resource->getRepository(['inherit' => true])->getCountryCode()) {
                $countryCode = " countrycode=\"{$country}\"";
            }

            if (null !== $agency = $this->resource->getRepository(['inherit' => true])->getIdentifier()) {
                $mainAgencyCode = " mainagencycode=\"{$agency}\"";
            }
        }

        if (null === $identifier = $this->resource->descriptionIdentifier) {
            $identifier = $this->resource->slug;
        }

        $url = $this->getResourceUrl();
        $encodinganalog = $this->getMetadataParameter('eadid');
        $sanitizedIdentifier = esc_specialchars($this->resource->identifier);
        $identifier = esc_specialchars($identifier);

        return "<eadid identifier=\"{$identifier}\"{$countryCode}{$mainAgencyCode} url=\"{$url}\" encodinganalog=\"{$encodinganalog}\">{$sanitizedIdentifier}</eadid>";
    }

    /**
     * Get the URL for the current resource.
     *
     * @return string URL of the resource
     */
    public function getResourceUrl()
    {
        // When running from the command line ("cli" or "worker" context) build the
        // resource URL from the siteBaseUrl setting
        if (
            in_array(
                sfContext::getInstance()->getConfiguration()->getEnvironment(),
                ['cli', 'worker']
            )
        ) {
            // Strip whitespace and "/" from the end of the Site Base URL
            $url = rtrim($this->siteBaseUrl, " \n\r\t\v\0/");

            // Get the "no_script_name" config for the "prod" environment
            $noScriptName = qubitConfiguration::getConfigForEnvironment(
                'no_script_name',
                'prod',
                'config/settings.yml'
            );

            // Add 'index.php' to the URL when "no_script_name" is not true in
            // production
            if (empty($noScriptName)) {
                $url .= '/index.php';
            }

            // Append the slug
            $url .= '/'.$this->resource->slug;

            return $url;
        }

        // When running in a web context, generate the resource URL using symfony
        // routing
        return url_for([$this->resource, 'module' => 'informationobject'], true);
    }

    public function renderEadNormalizedDate($date)
    {
        return str_replace('-', '', $date);
    }

    public static function renderEadDenormalizedDate($date)
    {
        $dateData = date_parse($date);

        $dateOutput = $dateData['year'];

        if ($dataData['month']) {
            $dateOutput .= '-'.$dateData['month'].'-';

            // if a month is specified, add day specification as well
            $dateOutput .= ($dateData['day']) ? $dateData['day'] : '01';
        }

        return $dateOutput;
    }

    public static function parseEadDenormalizedDateData($date)
    {
        $parsedData = [];
        $dates = explode('/', $date);

        $parsedData['start'] = sfEadPlugin::renderEadDenormalizedDate($dates[0]);

        if (count($dates) > 1) {
            $parsedData['end'] = sfEadPlugin::renderEadDenormalizedDate($dates[1]);
        }

        return $parsedData;
    }

    public function renderEadDateFromEvent($eventType, $event)
    {
        $output = '<date type="'.$eventType.'" ';

        // create normalized date/date range
        if ($event->startDate || $event->endDate) {
            $normalized = ($event->startDate) ? $this->renderEadNormalizedDate($event->startDate) : '';

            if ($event->endDate) {
                $normalized .= ($event->startDate) ? '/' : '';
                $normalized .= $this->renderEadNormalizedDate($event->endDate);
            }
        }

        // add normalized portion of date tag if it exists
        $output .= (isset($normalized)) ? 'normal="'.$normalized.'" ' : '';

        $output .= '>'.$event->date.'</date>';

        return $output;
    }

    public function getMetadataParameter($param)
    {
        $metadataStandard = sfConfig::get('app_default_template_informationobject');

        if (isset(self::$ENCODING_MAP[$metadataStandard][$param])) {
            return self::$ENCODING_MAP[$metadataStandard][$param];
        }

        return isset(self::$ENCODING_MAP['isad'][$param]) ? self::$ENCODING_MAP['isad'][$param] : '';
    }

    public function getEadContainerAttributes($physcalObject)
    {
        switch ($physcalObject->type) {
            case 'Cardboard box':
                $result = 'type="box" label="cardboard"';

                break;

            case 'Hollinger box':
                $result = 'type="box" label="hollinger"';

                break;

            case 'Filing cabinet':
                $result = 'type="cabinet" label="filing"';

                break;

            case 'Map cabinet':
                $result = 'type="cabinet" label="map"';

                break;

            default:
                $result = 'type="'.escape_dc(esc_specialchars(strtolower($physcalObject->type))).'"';
        }

        return $result;
    }

    public static function getUnitidValue($resource)
    {
        if (!isset($resource->identifier)) {
            return;
        }

        if (!sfConfig::get('app_inherit_code_informationobject', false)) {
            return $resource->identifier;
        }

        $identifier = [];
        foreach ($resource->ancestors->andSelf()->orderBy('lft') as $item) {
            if (isset($item->identifier)) {
                $identifier[] = $item->identifier;
            }
        }

        return implode(sfConfig::get('app_separator_character', '-'), $identifier);
    }

    /**
     * Get a string representation of the resource's level of description.
     *
     * @param mixed $resource
     * @param mixed $eadLevels
     */
    public static function renderLOD($resource, $eadLevels)
    {
        // Mapping of EAD levels to possible AtoM LOD names
        $variations = [
            'recordgrp' => ['record-group', 'record group', 'recordgroup'],
            'subfonds' => ['sous fonds', 'sous-fonds', 'sousfonds', 'sub-fonds', 'sub fonds'],
            'subgrp' => ['subgroup', 'sub-group', 'sub group'],
            'subseries' => ['sub-series', 'sub series'],
        ];
        $defaultLevel = 'otherlevel';
        $renderedLOD = '';
        $levelOfDescription = $defaultLevel;

        if ($resource->levelOfDescriptionId) {
            $levelOfDescription = strtolower($resource->getLevelOfDescription()->getName(['culture' => 'en']));

            // Check EAD levels variations
            foreach ($variations as $eadLevel => $lods) {
                // Use EAD level if LOD is one of its variations
                if (in_array($levelOfDescription, $lods)) {
                    $levelOfDescription = $eadLevel;

                    break;
                }
            }

            if (!in_array($levelOfDescription, $eadLevels)) {
                $renderedLOD = 'otherlevel="'.$levelOfDescription.'" ';
                $levelOfDescription = $defaultLevel;
            }
        }

        $renderedLOD .= 'level="'.$levelOfDescription.'"';

        return $renderedLOD;
    }

    /*
     * Get various <controlaccess> fields from specified information object.
     * @return  bool  True if there are controlaccess fields present, false if not
     */
    public function getControlAccessFields($io, &$materialTypes, &$genres, &$subjects, &$names, &$places, &$placeEvents)
    {
        $materialTypes = $io->getMaterialTypes();
        $genres = $io->getTermRelations(QubitTaxonomy::GENRE_ID);
        $subjects = $io->getSubjectAccessPoints();
        $names = $io->getNameAccessPoints();
        $places = $io->getPlaceAccessPoints();
        $placeEvents = $io->getPlaceAccessPoints(['events' => true]);

        // Special case: we don't add actors from creation events to <controlaccess>, to
        // prevent duplication during round tripping (AtoM will get the creator from <origination>).
        // So we need to take this into account for our return value when indicating if there are any
        // <controlaccess> fields or not.

        $hasNonCreationActorEvents = false;
        foreach ($io->getActorEvents() as $event) {
            if ('Creator' != $event->getType()->getRole()) {
                $hasNonCreationActorEvents = true;

                break;
            }
        }

        return count($materialTypes)
            || count($genres)
            || count($subjects)
            || count($names)
            || count($places)
            || $hasNonCreationActorEvents;
    }

    public function getPublicationDate()
    {
        // Create formated publication date
        // todo: use 'published at' date, see issue#902
        $date = strtotime($this->resource->getCreatedAt());

        return date('Y', $date).'-'.date('m', $date).'-'.date('d', $date);
    }
}
