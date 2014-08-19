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

    if (isset($cache) && $cache instanceof sfMemcacheCache)
    {
      $cache->set('updating_artwork', $id);
      $this->log('UpdateArtworkTMS - Artwork ID stored in cache');
    }
    else
    {
      $this->log(sprintf('UpdateArtworkTMS - Cache needs to be configured to use sfMemcacheCache'));
    }

    // Update artwork
    $fetchTms = new arFetchTms;
    $fetchTms->updateArtwork($artwork);

    // Remove artwork id from cache
    // This requires Symfony using sfMemcacheCache to work with the frontend
    if (isset($cache) && $cache instanceof sfMemcacheCache)
    {
      $cache->remove('updating_artwork');
      $this->log('UpdateArtworkTMS - Artwork ID removed from cache');
    }

    return $this->finishJob();
  }

  protected function finishJob()
  {
    $this->log('Job finished.');

    return true;
  }
}
