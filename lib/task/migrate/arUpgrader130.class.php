<?php

/*
 * This file is part of Qubit Toolkit.
 *
 * Qubit Toolkit is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Qubit Toolkit is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Qubit Toolkit.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Upgrade Qubit data from Release 1.3
 *
 * @package    qubit
 * @subpackage migration
 * @version    svn: $Id$
 */
class arUpgrader130
{
  const
    MILESTONE = '1.3',
    INIT_VERSION = 92;

  public function up($version, $configuration, $options)
  {
    if ($options['verbose'])
    {
      echo "up($version)\n";
    }

    switch ($version)
    {
      // Return false if no upgrade available
      default:

        return false;
    }

    return true;
  }
}
