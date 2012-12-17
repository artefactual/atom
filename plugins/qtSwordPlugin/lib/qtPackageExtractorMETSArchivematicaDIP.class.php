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

class qtPackageExtractorMETSArchivematicaDIP extends qtPackageExtractorBase
{
  protected function processDmdSec($xml, $informationObject)
  {
    $xml->registerXPathNamespace("m", "http://www.loc.gov/METS/");

    $dublincore = $xml->xpath('.//m:mdWrap/m:xmlData/*');
    $dublincore = end($dublincore);
    $dublincore->registerXPathNamespace("dc", "http://purl.org/dc/terms/");

    foreach ($dublincore as $item)
    {
      $value = trim($item->__toString());
      if (0 == strlen($value))
      {
        continue;
      }

      switch (str_replace('dcterms:', '', $item->getName()))
      {
        case 'title':
          $informationObject->setTitle($value);

          break;

        case 'creator':
          $informationObject->setActorByName($value, array('event_type_id' => QubitTerm::CREATION_ID));

          break;

        case 'coverage':
          $informationObject->setAccessPointByName($value, array('type_id' => QubitTaxonomy::PLACE_ID));

          break;

        case 'subject':
          $informationObject->setAccessPointByName($value, array('type_id' => QubitTaxonomy::SUBJECT_ID));

          break;

        case 'description':
          $informationObject->scopeAndContent = $value;

          break;

        case 'publisher':
          $informationObject->setActorByName($value, array('event_type_id' => QubitTerm::PUBLICATION_ID));

          break;

        case 'contributor':
          $informationObject->setActorByName($value, array('event_type_id' => QubitTerm::CONTRIBUTION_ID));

          break;

        case 'date':
          $informationObject->setDates($value);

          break;

        case 'type':
          foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DC_TYPE_ID) as $item)
          {
            if (strtolower($value) == strtolower($item->__toString()))
            {
              $relation = new QubitObjectTermRelation;
              $relation->term = $item;

              $informationObject->objectTermRelationsRelatedByobjectId[] = $relation;

              break;
            }
          }

          break;

        case 'format':
          $informationObject->extentAndMedium = $value;

          break;

        case 'identifier':
          $informationObject->identifier = $value;

          break;

        case 'source':
          $informationObject->locationOfOriginals = $value;

          break;

        case 'language':
          // TODO: the user could write "English" instead of "en"? (see symfony...widget/i18n/*)
          $informationObject->language = array($value);

          break;

        case 'isPartOf':
          // TODO: ?

          break;

        case 'rights':
          $informationObject->accessConditions = $value;

          break;
      }
    }

    return $informationObject;
  }

  protected function process()
  {
    // Find METS file
    if ($handle = opendir($this->filename))
    {
      while (false !== $entry = readdir($handle))
      {
        if (0 < preg_match('/^METS\..*\.xml$/', $entry))
        {
          $this->document = new SimpleXMLElement(@file_get_contents($this->filename.DIRECTORY_SEPARATOR.$entry));

          break;
        }
      }

      closedir($handle);
    }
    else
    {
      throw new sfException('METS XML file was not found.');
    }

    if (!isset($this->document))
    {
      throw new sfException('METS document could not be opened.');
    }

    $this->document->registerXPathNamespace('m', 'http://www.loc.gov/METS/');

    // AIP UUID
    $aipUUID = $this->getUUID($this->filename);

    // Main object
    if ($this->createParent && null != ($dmdSec = $this->getMainDmdSec()))
    {
      $this->resource = $this->processDmdSec($dmdSec, $this->resource);
    }

    $publicationStatus = sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID);

    foreach ($this->getFilesFromDirectory($this->filename.DIRECTORY_SEPARATOR.'/objects') as $item)
    {
      $parts = pathinfo($item);
      $filename = $parts['basename'];

      // Object UUID
      $objectUUID = $this->getUUID($filename);

      // Create child
      $child = new QubitInformationObject;
      $child->setPublicationStatus($publicationStatus);

      // Get title from filename, remove UUID (36 + hyphen)
      $child->title = substr($filename, 37);

      // Process metatadata from METS file
      if (null !== ($dmdSec = $this->searchFileDmdSec($objectUUID)))
      {
        $child = $this->processDmdSec($dmdSec, $child);
      }

      // Storage UUIDs
      $child->addProperty('objectUUID', $objectUUID);
      $child->addProperty('aipUUID', $aipUUID);

      // Add digital object
      $digitalObject = new QubitDigitalObject;
      $digitalObject->assets[] = new QubitAsset($item);
      $digitalObject->usageId = QubitTerm::MASTER_ID;
      $child->digitalObjects[] = $digitalObject;

      $this->resource->informationObjectsRelatedByparentId[] = $child;
    }

    $this->resource->save();

    parent::process();
  }

  protected function getMainDmdSec()
  {
    $items = $this->document->xpath('//m:mets/m:structMap/m:div/m:div');

    $id = $items[0]['DMDID'];

    $dmdSec = $this->document->xpath('//m:mets/m:dmdSec[@ID="'.$id.'"]');
    if (0 < count($dmdSec))
    {
      return $dmdSec[0];
    }
  }

  protected function searchFileDmdSec($uuid)
  {
    $node = $this->document->xpath('//m:mets/m:fileSec/m:fileGrp[@USE="original"]');

    if (empty($node))
    {
      return;
    }

    foreach ($node[0] as $item)
    {
      if (false !== strstr($item['ID'], $uuid))
      {
        $dmdSec = $this->document->xpath('//m:mets/m:dmdSec[@ID="'.$item['DMDID'].'"]');
        if (0 < count($dmdSec))
        {
          return $dmdSec[0];
        }
      }
    }
  }
}
