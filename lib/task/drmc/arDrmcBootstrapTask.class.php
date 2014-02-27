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
 * arElasticSearchPlugin main class
 *
 * @package     AccesstoMemory
 * @subpackage  search
 */
class arDrmcBootstrapTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('verbose', 'v', sfCommandOption::PARAMETER_NONE, 'If passed, progress is displayed for each object indexed')));

    $this->namespace = 'drmc';
    $this->name = 'bootstrap';

    $this->briefDescription = 'Bootstrap DRMC-MA database';
    $this->detailedDescription = <<<EOF
The [drmc:bootstrap|INFO] task adds the necessary initial data to your database
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    sfContext::createInstance($this->configuration);

    new sfDatabaseManager($this->configuration);

    // TODO
    $levels = [
      'Artwork record',
      'Description',
      'Physical component', // TODO: add extra types of physical components
      'Digital component',
      'Supporting technology record'
    ];
  }
}
