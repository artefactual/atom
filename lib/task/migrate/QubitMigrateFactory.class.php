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
 * Factory for running Qubit data migrations
 *
 * @package    qubit
 * @subpackage migration
 * @version    svn: $Id: QubitMigrateFactory.class.php 11614 2012-05-03 22:21:13Z david $
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitMigrateFactory
{
  public function getMigrator($data, $version)
  {
    if (preg_match('/^\d+$/', $version))
    {
       if (QubitMigrate108::FINAL_VERSION > intval($version))
       {
         return new QubitMigrate108($data, $version);
       }
       else if (QubitMigrate109::FINAL_VERSION > intval($version))
       {
         return new QubitMigrate109($data, $version);
       }
       else if (QubitMigrate110::FINAL_VERSION > intval($version))
       {
         return new QubitMigrate110($data, $version);
       }
    }
    else
    {
      $migrateClass = 'QubitMigrate'.str_replace('.', '', $version);

      return new $migrateClass($data, $version);
    }
  }
}
