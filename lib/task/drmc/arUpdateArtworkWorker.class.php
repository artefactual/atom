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

class arUpdateArtworkWorker extends Net_Gearman_Job_Common
{
  protected $dispatcher = null;

  protected function log($message)
  {
    $this->dispatcher->notify(new sfEvent($this, 'gearman.worker.log',
      array('message' => $message)));
  }

  public function run($id)
  {
    $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

    $this->log('A new job has started to being processed.');

    // Clear cache of all classes
    foreach (array(
      'QubitAccessLog',
      'QubitActorI18n',
      'QubitContactInformation',
      'QubitContactInformationI18n',
      'QubitEventI18n',
      'QubitFunctionI18n',
      'QubitInformationObjectI18n',
      'QubitKeymap',
      'QubitMenu',
      'QubitMenuI18n',
      'QubitNote',
      'QubitNoteI18n',
      'QubitOaiHarvest',
      'QubitOaiRepository',
      'QubitObject',
      'QubitOtherName',
      'QubitOtherNameI18n',
      'QubitPhysicalObjectI18n',
      'QubitProperty',
      'QubitPropertyI18n',
      'QubitRelationI18n',
      'QubitRepositoryI18n',
      'QubitRightsI18n',
      'QubitSetting',
      'QubitSettingI18n',
      'QubitSlug',
      'QubitStaticPageI18n',
      'QubitStatus',
      'QubitTaxonomyI18n',
      'QubitTermI18n') as $className)
    {
      $className::clearCache();
    }

    if (null === $artwork = QubitInformationObject::getById($id))
    {
      $this->log('UpdateArtworkTMS - Information object not found');

      return $this->finishJob();
    }

    if ($artwork->levelOfDescriptionId != sfConfig::get('app_drmc_lod_artwork_record_id'))
    {
      $this->log('UpdateArtworkTMS - Status not available for this level of description');

      return $this->finishJob();
    }

    if (null === $artwork->identifier)
    {
      $this->log('UpdateArtworkTMS - TMS object ID not found');

      return $this->finishJob();
    }

    $this->log(sprintf('UpdateArtworkTMS - Artwork ID: %s', $artwork->id));

    // Store artwork being updated in cache
    // This requires Symfony using sfMemcacheCache to work with the frontend
    try
    {
      $cache = QubitCache::getInstance();
    }
    catch (Exception $e)
    {
      $this->log(sprintf('UpdateArtworkTMS - Cache could not be accessed: %s', $e->getMessage()));
    }

    if (isset($cache))
    {
      $cache->set('updating_artwork', $id);
      $this->log('UpdateArtworkTMS - Artwork ID stored in cache');
    }

    // Update artwork
    list($tmsComponentsIds, $artworkThumbnail) = arFetchTms::getTmsObjectData($artwork, $artwork->identifier);

    // Get intermediate level
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $artwork->id);
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, sfConfig::get('app_drmc_lod_description_id'));
    $components = QubitInformationObject::getOne($criteria);

    // Get actual artwork components in DRMC
    $componentsLevels = array(
      sfConfig::get('app_drmc_lod_archival_master_id'),
      sfConfig::get('app_drmc_lod_artist_supplied_master_id'),
      sfConfig::get('app_drmc_lod_artist_verified_proof_id'),
      sfConfig::get('app_drmc_lod_exhibition_format_id'),
      sfConfig::get('app_drmc_lod_miscellaneous_id'),
      sfConfig::get('app_drmc_lod_component_id')
    );

    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::LFT, $artwork->lft, Criteria::GREATER_THAN);
    $criteria->add(QubitInformationObject::RGT, $artwork->rgt, Criteria::LESS_THAN);
    $criteria->add(QubitInformationObject::LEVEL_OF_DESCRIPTION_ID, $componentsLevels, Criteria::IN);

    $tmsComponentsIoIds = array();
    foreach (QubitInformationObject::get($criteria) as $component)
    {
      // Update or delete actual components
      if (isset($component->identifier) && false !== $key = array_search($component->identifier, $tmsComponentsIds))
      {
        // Update
        $tmsComponentsIoIds[] = arFetchTms::getTmsComponentData($component, $component->identifier, $artworkThumbnail);

        // Remove from array
        unset($tmsComponentsIds[$key]);
      }
      else
      {
        // Move childs to parent of the component
        foreach ($component->getChildren() as $child)
        {
          $child->parentId = $component->parentId;
        }

        // Delete (this also deletes relations with AIPs and other components)
        $component->delete();
      }
    }

    // Create new components with the remaining TMS ids in the array
    foreach ($tmsComponentsIds as $tmsId)
    {
      $tmsComponent = new QubitInformationObject;
      $tmsComponent->parentId = isset($components) ? $components->id : $artwork->id;
      $tmsComponent->levelOfDescriptionId = sfConfig::get('app_drmc_lod_component_id');
      $tmsComponent->setPublicationStatusByName('Published');

      // Update TMS Component data
      $tmsComponentsIoIds[] = arFetchTms::getTmsComponentData($tmsComponent, $tmsId, $artworkThumbnail);
    }

    // Save info object components ids as property of the artwork
    // because they are not directly related but added as part of the artwork in ES
    $property = $artwork->getPropertyByName('childComponents');
    $property->setName('childComponents');
    $property->setValue(serialize($tmsComponentsIoIds));
    $property->setObjectId($artwork->id);
    $property->save();

    // Update non already updated descendants in ES
    $sql = <<<sql

SELECT
  id
FROM
  information_object
WHERE
  lft > ?
AND
  rgt < ?;

sql;

    $results = QubitPdo::fetchAll($sql, array($artwork->lft, $artwork->rgt));

    foreach ($results as $item)
    {
      if (!in_array($item->id, $tmsComponentsIoIds))
      {
        $node = new arElasticSearchInformationObjectPdo($item->id);
        $data = $node->serialize();

        QubitSearch::getInstance()->addDocument($data, 'QubitInformationObject');
      }
    }

    // Update artwork AIPs in ES
    $sql = <<<sql

SELECT
  id
FROM
  aip
WHERE
  part_of = ?;

sql;

    $results = QubitPdo::fetchAll($sql, array($artwork->id));

    foreach ($results as $item)
    {
      $node = new arElasticSearchAipPdo($item->id);
      $data = $node->serialize();

      QubitSearch::getInstance()->addDocument($data, 'QubitAip');
    }

    // Add components data for the artwork in ES
    QubitSearch::getInstance()->update($artwork);

    // Remove artwork id from cache
    // This requires Symfony using sfMemcacheCache to work with the frontend
    if (isset($cache))
    {
      $cache->remove('updating_artwork');
      $this->log('UpdateArtworkTMS - Artwork ID removed from cache');
    }

    // Save ES documents in the batch queue
    // We need to call the magic method explictly
    // because the object isn't destroyed in a worker
    QubitSearch::getInstance()->__destruct();

    return $this->finishJob();
  }

  protected function finishJob()
  {
    $this->log('Job finished.');

    return true;
  }
}
