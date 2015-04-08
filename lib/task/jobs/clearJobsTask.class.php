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
 * Clear the AtoM jobs table
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class clearJobsTask extends sfBaseTask
{
    protected $namespace        = 'jobs';
    protected $name             = 'clear';
    protected $briefDescription = 'Clear AtoM jobs';

    protected $detailedDescription = <<<EOF
Clears jobs
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
      new sfCommandOption('no-confirmation', 'B', sfCommandOption::PARAMETER_NONE, 'Do not ask for confirmation'),
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

    $sql = 'SELECT count(1) FROM job WHERE status_id=?';
    $runningJobCount = QubitPdo::fetchColumn($sql, array(QubitTerm::JOB_STATUS_IN_PROGRESS_ID));
    
    if ($runningJobCount > 0)
    {
      print "WARNING: AtoM reports there are jobs currently running. It is *highly* recommended you make sure ".
            "there aren't any jobs actually running.\n\n";
    }

    // Confirmation
    $question = 'Are you SURE you want to clear all jobs in the database? (y/N)';
    if (!$options['no-confirmation'] && !$this->askConfirmation(array($question), 'QUESTION_LARGE', false))
    {
      $this->logSection('jobs:clear', 'Aborting.');
      return 1;
    }

    $jobs = QubitJob::getAll();
    foreach ($jobs as $job)
    {
      $job->delete();
    }

    $this->logSection('jobs:clear', 'All jobs cleared successfully!');
  }
}
