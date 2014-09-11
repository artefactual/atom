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
 * @package    AccesstoMemory
 * @subpackage sfEadPlugin
 * @author     David Juhasz <david@artefactual.com>
 */
class sfEadPlugin
{
  public
    $resource,
    $siteBaseUrl;

  public static
    $ENCODING_MAP = array(
      'isad' => array(
        'relatedencoding'       => 'ISAD(G)v2',
        'eadid'                 => 'identifier',
        'titleproper'           => 'title',
        'author'                => 'creator',
        'publisher'             => 'publisher',
        'date'                  => 'date',
        'language'              => 'language',
        'languageOfDescription' => 'languageOfDescription',
        'script'                => 'script',
        'scriptOfDescription'   => 'scriptOfDescription',
        'descrules'             => '3.7.2',
        'scopecontent'          => '3.3.1',
        'arrangement'           => '3.3.4',
        'phystech'              => '3.4.3',
        'appraisal'             => '3.3.2',
        'acqinfo'               => '3.2.4',
        'accruals'              => '3.3.3',
        'custodhist'            => '3.2.3',
        'originalsloc'          => '3.5.1',
        'altformavail'          => '3.5.2',
        'relatedmaterial'       => '3.5.3',
        'accessrestrict'        => '3.4.1',
        'userestrict'           => '3.4.2',
        'otherfindaid'          => '3.4.5',
        'bibliography'          => '3.5.4',
        'unittitle'             => '3.1.2',
        'unitid'                => '3.1.1',
        'unitdate'              => '3.1.3',
        'extent'                => '3.1.5',
        'langmaterial'          => '3.4.3',
        'note'                  => '3.6.1',
        'bioghist'              => '3.2.2',
        'origination'           => '3.2.1'),
      'rad' => array(
        'relatedencoding'       => 'RAD',
        'eadid'                 => 'identifier',
        'titleproper'           => 'title',
        'author'                => 'creator',
        'publisher'             => 'publisher',
        'date'                  => 'date',
        'language'              => 'language',
        'languageOfDescription' => 'languageOfDescription',
        'script'                => 'script',
        'scriptOfDescription'   => 'scriptOfDescription',
        'scopecontent'          => '1.7D',
        'arrangement'           => '1.8B13',
        'phystech'              => '1.8B9a',
        'acqinfo'               => '1.8B12',
        'accruals'              => '1.8B19',
        'custodhist'            => '1.7C',
        'originalsloc'          => '1.8B15a',
        'altformavail'          => '1.8B15b',
        'relatedmaterial'       => '1.8B18',
        'accessrestrict'        => '1.8B16a',
        'userestrict'           => '1.8B16c',
        'otherfindaid'          => '1.8B17',
        'unittitle'             => '1.1B',
        'unitid'                => '1.8B11',
        'unitdate'              => '1.4B2',
        'extent'                => '1.5B1',
        'langmaterial'          => '1.8B9a',
        'note'                  => '1.8B21',
        'bioghist'              => '1.7B',
        'origination'           => '1.7C',
        'genreform'         => '1.1C',
        'parallel'                  => '1.1D',
        'otherinfo'              => '1.1E',
        'statrep'               => '1.1F',
        'titlevariation'              => '1.8B1',
        'titleattributions'           => '1.8B6',
        'titlecontinuation'         => '1.8B4',
        'titlestatrep'                  => '1.8B5',
        'titleparallel'              => '1.8B3',
        'titlesource'           => '1.8B2',
        'editionstatement'        => '1.2B1',
        'statementofresp'        => '1.2C',
        'cartographic'                  => '5.3B1',
        'projection'              => '5.3C1',
        'coordinates'           => '5.3D',
        'architectural'        => '6.3B',
        'philatelic'        => '12.3B1',
        'titleProperOfPublishersSeries' => '1.6B1',
        'parallelTitleOfPublishersSeries' => '1.6C1',
        'otherTitleInformationOfPublishersSeries' => '1.6D1',
        'statementOfResponsibilityRelatingToPublishersSeries' => '1.6E1',
        'numberingWithinPublishersSeries' => '1.6F',
        'standardNumber' => '1.9B1',
        'bibseries'           => '1.8B10',
        'edition'        => '1.8B7',
        'physdesc'        => '1.8B9',
        'conservation'      => '1.8B9b',
        'material'              => '1.5E',
        'alphanumericdesignation' => '1.8B11',
        'rights'        => '1.8B16b',
        'general'        => '1.8B21',
        'actorEventsName'   => '1.4D'));

  public function __construct(QubitInformationObject $resource)
  {
    $this->resource = $resource;

    $this->version = 'Access to Memory (AtoM) '.qubitConfiguration::VERSION;
  }

  public function __get($name)
  {
    return $this->resource->$name;
  }

  public function subjectHasNonBlankSourceNotes(&$subject)
  {
    $hasNonBlankNotes = false;

    $notes = $subject->getTerm()->getSourceNotes();
    foreach($notes as $note)
    {
      if ($note != '')
      {
        $hasNonBlankNotes = true;
      }
    }

    return $hasNonBlankNotes;
  }

  public function getAssetPath($do, $getReference = false)
  {
    if ($getReference)
    {
      $do = $do->reference;
    }

    if ($this->siteBaseUrl !== false)
    {
      return $this->siteBaseUrl . ltrim($do->getFullPath(), '/');
    }

    return public_path($do->getFullPath(), true);
  }

  public function renderEadId($siteBaseUrl = false)
  {
    $countryCode = $mainAgencyCode = '';
    $this->siteBaseUrl = $siteBaseUrl;

    if (null !== $this->resource->getRepository(array('inherit' => true)))
    {
      if (null !== $country = $this->resource->getRepository(array('inherit' => true))->getCountryCode())
      {
        $countryCode = " countrycode=\"$country\"";
      }

      if (null !== $agency = $this->resource->getRepository(array('inherit' => true))->getIdentifier())
      {
        if (isset($country))
        {
          $agency = $country.'-'.$agency;
        }

        $mainAgencyCode = " mainagencycode=\"$agency\"";
      }
    }

    $url = ($siteBaseUrl)
      ? $siteBaseUrl . $this->resource->slug
      : url_for(array($this->resource, 'module' => 'informationobject'), $absolute = true);

    if (null === $identifier = $this->resource->descriptionIdentifier)
    {
      $identifier = $this->resource->slug;
    }

    $encodinganalog = $this->getMetadataParameter('eadid');

    $ret = "<eadid identifier=\"$identifier\"$countryCode$mainAgencyCode url=\"$url\" encodinganalog=\"$encodinganalog\">{$this->resource->identifier}</eadid>";
    return esc_specialchars($ret);
  }

  public function renderEadNormalizedDate($date)
  {
    return str_replace('-', '', $date);
  }

  public static function renderEadDenormalizedDate($date)
  {
    $dateData   = date_parse($date);

    $dateOutput = $dateData['year'];

    if ($dataData['month'])
    {
      $dateOutput .= '-'. $dateData['month'] .'-';

      // if a month is specified, add day specification as well
      $dateOutput .= ($dateData['day']) ? $dateData['day'] : '01';
    }

    return $dateOutput;
  }

  public static function parseEadDenormalizedDateData($date)
  {
    $parsedData = array();
    $dates = explode('/', $date);

    $parsedData['start'] = sfEadPlugin::renderEadDenormalizedDate($dates[0]);

    if (count($dates) > 1)
    {
      $parsedData['end'] = sfEadPlugin::renderEadDenormalizedDate($dates[1]);
    }

    return $parsedData;
  }

  public function renderEadDateFromEvent($eventType, $event)
  {
    $output = '<date type="'. $eventType .'" ';

    // create normalized date/date range
    if ($event->startDate || $event->endDate)
    {
      $normalized = ($event->startDate) ? $this->renderEadNormalizedDate($event->startDate) : '';

      if ($event->endDate)
      {
        $normalized .= ($event->startDate) ? '/' : '';
        $normalized .= $this->renderEadNormalizedDate($event->endDate);
      }
    }

    // add normalized portion of date tag if it exists
    $output .= (isset($normalized)) ? 'normal="'. $normalized .'" ' : '';

    $output .= '>'. $event->date .'</date>';

    return $output;
  }

  public function getMetadataParameter($param)
  {
    $metadataStandard = sfConfig::get('app_default_template_informationobject');

    if (isset(self::$ENCODING_MAP[$metadataStandard][$param]))
    {
      return self::$ENCODING_MAP[$metadataStandard][$param];
    }

    return isset(self::$ENCODING_MAP['isad'][$param]) ? self::$ENCODING_MAP['isad'][$param] : '';
  }

  public function getEadContainerAttributes($physcalObject)
  {
    switch ($physcalObject->type)
    {
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

  public function renderEadPhysDesc($extentAndMedium)
  {
    $dlStartTag = '<dl>';
    $dlEndTag = '</dl>';

    $physDescContent = '';
    $extentAndMedium = trim(esc_specialchars($extentAndMedium));

    $dlPos = strpos($extentAndMedium, $dlStartTag);
    $dlEndPos = strpos($extentAndMedium, $dlEndTag);

    // Check if extentAndMedium contains a HTML definition list
    if ($dlPos === false)
    {
      $physDescContent .= '<extent encodinganalog="' . $this->getMetadataParameter('extent') . '">' . escape_dc(esc_specialchars($extentAndMedium)) . '</extent>';
    }
    else // AtoM uses <dl> / <dd> / <dt> tags to specify children of <extent>.
    {
      // Check for free text before <dl>
      if ($dlPos !== 0)
      {
        $physDescContent = substr($extentAndMedium, 0, $dlPos);
      }

      if ($dlPos === false || $dlEndPos === false)
      {
        throw new sfException('Mismatching <dl></dl> tags in EAD found');
      }

      // Only parse part between <dl>...</dl> tags in the XML parser
      $dlXml = substr($extentAndMedium, $dlPos, $dlEndPos - $dlPos + strlen($dlEndTag));
      $extentXml = new SimpleXMLIterator($dlXml);
      $extentTag = '';

      foreach ($extentXml as $extentElement)
      {
        if ($extentElement->getName() === 'dt')
        {
          $extentTag = $extentElement->__toString();
        }
        elseif ($extentElement->getName() === 'dd')
        {
          switch (strtolower($extentTag))
          {
            case 'extent':
              $physDescContent .= '<extent encodinganalog="' . $this->getMetadataParameter('extent') . '">';
              $physDescContent .= $extentElement->__toString() . '</extent>';
              break;

            case 'form of material':
              $physDescContent .= '<genreform>' . $extentElement->__toString() . "</genreform>";
              break;

            case 'physical facet':
              $physDescContent .= '<physfacet>' . $extentElement->__toString() . "</physfacet>";
              break;

            case 'dimensions':
              $physDescContent .= '<dimensions>' . $extentElement->__toString() . "</dimensions>";
              break;

            default:
              throw new sfException("Invalid extent tag $extentTag found");
          }
        }
      }

      // Check for free text after <dl>
      if ($dlEndPos && $dlEndPos !== strlen($extentAndMedium))
      {
        $physDescContent .= substr($extentAndMedium, $dlEndPos + strlen($dlEndTag));
      }
    }

    return $physDescContent;
  }

  public static function getUnitidValue($resource)
  {
    if (!isset($resource->identifier))
    {
      return;
    }

    if (!sfConfig::get('app_inherit_code_informationobject', false))
    {
      return $resource->identifier;
    }

    $identifier = array();
    foreach ($resource->ancestors->andSelf()->orderBy('lft') as $item)
    {
      if (isset($item->identifier))
      {
        $identifier[] = $item->identifier;
      }
    }

    return implode(sfConfig::get('app_separator_character', '-'), $identifier);
  }
}
