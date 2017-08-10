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
 * Cycle through all information objects and export their EAD and DC XML
 * representations as files
 *
 * @package     AccesstoMemory
 * @subpackage  cache
 */
class arCacheDescriptionXmlTask extends arBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', 'qubit'),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'cli'),
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
      new sfCommandOption('skip', null, sfCommandOption::PARAMETER_OPTIONAL, 'Number of information objects to skip', 0)
    ));

    $this->namespace = 'cache';
    $this->name = 'xml-representations';

    $this->briefDescription = 'Render all descriptions as XML and cache the results as files';
    $this->detailedDescription = <<<EOF
Render all descriptions as XML and cache the results as files
EOF;
  }

  public function execute($arguments = array(), $options = array())
  {
    parent::execute($arguments, $options);
    $this->exportAll($options);
  }

  private function exportAll($options)
  {
    $logger = new sfCommandLogger(new sfEventDispatcher);
    $logger->log('Caching XML representations of information objects...');

    $cache = new QubitInformationObjectXmlCache(array('logger' => $logger));
    $cache->exportAll(array('skip' => $options['skip']));

    $logger->log('Done.');
  }
}
