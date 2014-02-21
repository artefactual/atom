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
 * Import csv data
 *
 * @package    symfony
 * @subpackage task
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class goatTask extends csvImportBaseTask
{
    protected $namespace        = 'csv';
    protected $name             = 'goat';
    protected $briefDescription = 'Goat';

    protected $detailedDescription = <<<EOF
Goat
EOF;

  /**
   * @see sfTask
   */
  public function execute($arguments = array(), $options = array())
  {
sfContext::createInstance($this->configuration);

// Store AIP data
$aip = new QubitAip;
$aip->typeId = QubitTerm::ARTWORK_COMPONENT_ID; // TODO: Get AIP type from METS
$aip->uuid = gen_uuid();
$aip->filename = 'Bob';
$aip->digitalObjectCount = 5;
#$aip->partOf = 1;

$aip->sizeOnDisk = 1000;
$aip->createdAt = '2010-01-01 12:10:34 PST';

$aip->save();


  }
}

function gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}

?>
