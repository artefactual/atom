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
 * Generate  identify response of the OAI-PMH protocol for the qubit toolkit
 *
 * @package    qubit
 * @subpackage oai
 * @version    svn: $Id: identifyComponent.class.php 10288 2011-11-08 21:25:05Z mj $
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class qtOaiPluginIdentifyComponent extends sfComponent
{
  public function execute($request)
  {
    $request->setRequestFormat('xml');
    $this->date = gmdate('Y-m-d\TH:i:s\Z');
    $this->title = sfconfig::get('app_siteTitle');
    $this->description = sfconfig::get('app_siteDescription');
    $this->protocolVersion = '2.0';

    list ($this->earliestDatestamp) = Propel::getConnection()->query('SELECT MIN('.QubitObject::UPDATED_AT.') FROM '.QubitObject::TABLE_NAME)->fetch();

    $this->granularity = 'YYYY-MM-DDThh:mm:ssZ';
    $this->deletedRecord = 'no';
    $this->compression = 'gzip';
    $this->path = url_for('oai/oaiAction');
    $this->attributes = $this->request->getGetParameters();

    $this->attributesKeys = array_keys($this->attributes);
    $this->requestAttributes = '';
    foreach ($this->attributesKeys as $key)
    {
      $this->requestAttributes .= ' '.$key.'="'.$this->attributes[$key].'"';
    }

    $criteria = new Criteria;
    $criteria->add(QubitAclUserGroup::GROUP_ID, QubitAclGroup::ADMINISTRATOR_ID);
    $criteria->addJoin(QubitAclUserGroup::USER_ID, QubitUser::ID);
    $users = QubitUser::get($criteria);
    $this->adminEmail = array();

    foreach ($users as $user)
    {
      $this->adminEmail[] = $user->getEmail()."\n";
    }
  }
}
