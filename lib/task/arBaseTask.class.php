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

abstract class arBaseTask extends sfBaseTask
{
  const MAX_LINE_SIZE = 2048;

  /**
   * @see sfCommandApplicationTask
   */
  public function __construct(sfEventDispatcher $dispatcher, sfFormatter $formatter)
  {
    parent::__construct($dispatcher, $formatter);

    $formatter->setMaxLineSize(self::MAX_LINE_SIZE);
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'cli', false);
    $this->context = sfContext::createInstance($configuration);
    sfConfig::add(QubitSetting::getSettingsArray());
  }
}
