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
 * Generate  identify response of the OAI-PMH protocol for the Access to Memory (AtoM)
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class arOaiPluginIdentifyComponent extends arOaiPluginComponent
{
  public function execute($request)
  {
    $this->title = sfconfig::get('app_siteTitle');
    $this->description = sfconfig::get('app_siteDescription');
    $this->protocolVersion = '2.0';

    list ($this->earliestDatestamp) = Propel::getConnection()->query('SELECT MIN('.QubitObject::UPDATED_AT.') FROM '.QubitObject::TABLE_NAME)->fetch();
    $this->earliestDatestamp = date_format(date_create($this->earliestDatestamp), 'Y-m-d\TH:i:s\Z');

    $this->granularity = 'YYYY-MM-DDThh:mm:ssZ';
    $this->deletedRecord = 'no';
    $this->compression = 'gzip';

    $this->setRequestAttributes($request);

    $this->adminEmails = array();
    if ((null !== $adminEmailsSetting = QubitSetting::getByName('oai_admin_emails'))
        && $adminEmailsValue = $adminEmailsSetting->getValue(array('sourceCulture' => true)))
    {
      $this->adminEmails = explode(',', $adminEmailsValue);
    }
  }
}
