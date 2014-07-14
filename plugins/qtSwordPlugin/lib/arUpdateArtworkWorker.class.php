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

    /*
    // Store artwork being updated in cache
    // This is not working as APC uses diferents caches for php-cli and Apache
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
    */

    // Store artwork being updated in cache.
    // Using APC stream dump so it can be accessed from CLI and Apache
    $cacheFileLoaded = false;

    try
    {
      // Check if dump file exists
      if (ini_get('apc.enable_cli') && false !== $dump_file = stream_resolve_include_path('apc.dump'))
      {
        // Load file dump
        if (false !== apc_bin_loadfile($dump_file))
        {
          // Save updating artwork and dump file
          apc_store('updating_artwork', $id);
          apc_bin_dumpfile(array(), null, 'apc.dump');

          $cacheFileLoaded = true;
        }
      }
    }
    catch (Exception $e)
    {
      $this->log(sprintf('UpdateArtworkTMS - Cache could not be accessed: %s', $e->getMessage()));
    }

    // Update artwork
    list($tmsComponentsIds, $artworkThumbnail) = arFetchTms::getTmsObjectData($artwork, $artwork->identifier);

    // Update all descendants in ES
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
      $node = new arElasticSearchInformationObjectPdo($item->id);
      $data = $node->serialize();

      QubitSearch::getInstance()->addDocument($data, 'QubitInformationObject');
    }

    // TODO:
    // - Fix nested set problem
    // - Update TMS data for components
    // - Update artwork AIPs
    // - Update relations

    /*
    // Remove artwork id from cache
    // This is not working as APC uses diferents caches for php-cli and Apache
    if (isset($cache))
    {
      $cache->remove('updating_artwork');
      $this->log('UpdateArtworkTMS - Artwork ID removed from cache');
    }
    */

    // Remove artwork id from cache
    if ($cacheFileLoaded)
    {
      // Remove updating artwork and dump file. Not working with apc_delete() and apc_delete_file()
      apc_store('updating_artwork', null);
      apc_bin_dumpfile(array(), null, 'apc.dump');
    }

    return $this->finishJob();
  }

  protected function finishJob()
  {
    $this->log('Job finished.');

    return true;
  }
}
