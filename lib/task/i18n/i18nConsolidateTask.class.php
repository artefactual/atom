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
 * Restore i18n strings lost when XLIFF files were broken into plugin-specific
 * directories
 *
 * @package    AccesstoMemory
 * @subpackage task
 * @author     David Juhasz <david@artefactual.com>
 */
class i18nConsolidateTask extends sfBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('culture', sfCommandArgument::REQUIRED, 'Message culture'),
      new sfCommandArgument('target', sfCommandArgument::REQUIRED, 'Target directory')
    ));

    $this->addOptions(array(
      // http://trac.symfony-project.org/ticket/8352
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_REQUIRED, 'The application name', true),
    ));

    $this->namespace = 'i18n';
    $this->name = 'consolidate';
    $this->briefDescription = 'Combine all application messages into a single output (XLIFF)';

    $this->detailedDescription = <<<EOF
Combine all application messages into a single output (XLIFF) file for ease of
use by translators.
EOF;
  }

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
    if (!file_exists($arguments['target']))
    {
      throw new sfException('Target directory "'.$arguments['target'].'" doesn\t exist');
    }

    $this->logSection('i18n', sprintf('Consolidating "%s" i18n messages', $arguments['culture']));

    $i18n = new sfI18N($this->configuration, new sfNoCache(), array('source' => 'XLIFF', 'debug' => false));
    $extract = new QubitI18nConsolidatedExtract($i18n, $arguments['culture'], array('target' => $arguments['target']));
    $extract->extract();
    $extract->save();
  }
}
