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

class arJobLogger extends sfLogger
{
  public function initialize(sfEventDispatcher $dispatcher, $options = array())
  {
    $this->dispatcher = $dispatcher;
    $this->options = $options;

    if (isset($this->options['level']))
    {
      $this->setLogLevel($this->options['level']);
    }

    if (!isset($this->options['job']))
    {
      throw new sfException('Missing job parameter');
    }

    $this->job = $this->options['job'];
  }

  protected function doLog($message, $priority)
  {
    $fMessage = sprintf('[%s] [%s] Job %d "%s": %s',
      $this->getPriorityName($priority),
      date('Y-m-d H:i:s'),
      $this->job->id,
      $this->job->name,
      $message);

    // TEXT type cannot have a default (i.e. ''), so use CONCAT_WS because it can work with null values
    // and coerce them into a string with an empty string separater.
    $sql = 'UPDATE job SET output = CONCAT_WS("", output, ?, "\n") WHERE id = ?';
    QubitPdo::prepareAndExecute($sql, array($fMessage, $this->job->id));

    // Forward to `gearman.worker.log` observers, jobWorkerTask will log to console via sfTask.
    $this->dispatcher->notify(new sfEvent($this, 'gearman.worker.log', array('message' => $message)));
  }
}
