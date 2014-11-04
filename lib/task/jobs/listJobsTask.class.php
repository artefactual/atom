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
 * List AtoM job information
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class listJobsTask extends sfBaseTask
{
    protected $namespace        = 'jobs';
    protected $name             = 'list';
    protected $briefDescription = 'List AtoM jobs';

    protected $detailedDescription = <<<EOF
List AtoM jobs. If no options are set it will list ALL the jobs.
EOF;

  /**
   * @see sfBaseTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('completed', null, sfCommandOption::PARAMETER_NONE, 'List only completed jobs'),
      new sfCommandOption('running', null, sfCommandOption::PARAMETER_NONE, 'List only running jobs'),
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    // initialized data connection in case it's needed
    $sf_context = sfContext::createInstance($this->configuration);
    $databaseManager = new sfDatabaseManager($this->configuration);
    $conn = $databaseManager->getDatabase('propel')->getConnection();

    $criteria = new Criteria;
    if ($options['completed'])
    {
      $criteria->add(QubitJob::STATUS_ID, QubitTerm::JOB_STATUS_COMPLETED_ID);
      $criteria->add(QubitJob::STATUS_ID, QubitTerm::JOB_STATUS_ERROR_ID);
    }

    if ($options['running'])
    {
      $criteria->add(QubitJob::STATUS_ID, QubitTerm::JOB_STATUS_IN_PROGRESS_ID);
    }

    $jobs = QubitJob::get($criteria);
    foreach ($jobs as $job)
    {
      print "$job->name\n";
      print " Status: " . $job->getStatusString() . "\n";
      print " Started: " . $job->getCreationDateString() . "\n";
      print " Completed: " . $job->getCompletionDateString() . "\n";
      print " User: " . QubitJob::getUserString($job) . "\n\n";
    }
  }
}
