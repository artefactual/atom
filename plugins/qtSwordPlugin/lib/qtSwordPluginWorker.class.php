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

class qtSwordPluginWorker extends arBaseJob
{
  /**
   * @see arBaseJob::$requiredParameters
   */
  protected $extraRequiredParameters = array('information_object_id');

  public function runJob($package)
  {
    if (isset($package['location']))
    {
      $this->info('A package was deposited by reference.');
      $this->info(sprintf('Location: %s', $package['location']));
    }
    else if (isset($package['filename']))
    {
      $this->info('A package was deposited by upload.');
    }

    $this->info('Processing...');

    $resource = QubitInformationObject::getById($package['information_object_id']);

    $this->info(sprintf('Object slug: %s', $resource->slug));

    $extractor = qtPackageExtractorFactory::build($package['format'],
      $package + array('resource' => $resource, 'job' => $job));

    $extractor->run();

    $this->job->setStatusCompleted();
    $this->job->save();

    return true;
  }
}
