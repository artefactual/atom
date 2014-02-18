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

class qtSwordPluginWorker extends Net_Gearman_Job_Common
{
  protected $dispatcher = null;

  protected function log($message)
  {
    $this->dispatcher->notify(new sfEvent($this, 'gearman.worker.log',
      array('message' => $message)));
  }

  public function run($package)
  {
    $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

    $this->log('A new job has started to being processed.');

    if (!is_writable(sfConfig::get('sf_web_dir').DIRECTORY_SEPARATOR.sfConfig::get('app_upload_dir')))
    {
      throw new sfException('ERROR: Read-write access needed in {sf_web_dir}/{app_upload_dir}!');
    }

    if (isset($package['location']))
    {
      $this->log(sprintf('A package was deposited by reference.'));
      $this->log(sprintf('Location: %s', $package['location']));
    }
    else if (isset($package['filename']))
    {
      $this->log(sprintf('A package was deposited by upload.'));
    }

    $this->log(sprintf('Processing...'));

    try
    {
      $resource = QubitInformationObject::getById($package['information_object_id']);

      $this->log(sprintf('Object slug: %s', $resource->slug));

      $extractor = qtPackageExtractorFactory::build($package['format'],
        $package + array('resource' => $resource, 'job' => $job));

      $extractor->run();
    }
    catch (Exception $e)
    {
      $this->log(sprintf('Exception: %s', $e->getMessage()));
    }

    // Save ES documents in the batch queue
    // We need to call the magic method explictly
    // because the object isn't destroyed in a worker
    QubitSearch::getInstance()->__destruct();

    $this->log(sprintf('Job finished.'));

    return true;
  }
}
