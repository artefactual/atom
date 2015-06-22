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
 * Export repository information to a CSV
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Gale <mikeg@artefactual.com>
 */
class exportRepositoriesTask extends arBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'repository-export';
    protected $briefDescription = 'Export repository information to a CSV';

    protected $detailedDescription = <<<EOF
Export repository information to a CSV.
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
    ));

    $this->addArguments(array(
      new sfCommandArgument('filename', sfCommandArgument::REQUIRED, 'Filename for the CSV')
    ));
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    $writer = new csvRepositoryExport($arguments['filename']);

    foreach ($this->getRepositories() as $r)
    {
      $this->logSection('csv', 'exporting '.$r->authorizedFormOfName);
      $writer->exportResource($r);
    }
  }

  private function getRepositories()
  {
    $criteria = new Criteria;
    $criteria->add(QubitRepository::ID, QubitRepository::ROOT_ID, Criteria::NOT_EQUAL);

    return QubitRepository::get($criteria);
  }

}
