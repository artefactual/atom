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

  public function run($tmsId)
  {
    $this->dispatcher = sfContext::getInstance()->getEventDispatcher();

    $this->log('A new job has started to being processed.');
    $this->log(sprintf('TMSObject ID: %s', $tmsId));
    $this->log('Job finished.');

    return true;
  }
}
