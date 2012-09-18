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
 * Generate the OAI-PMH response
 *
 * @package    qubit
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class qtOaiPluginHarvesterListAction extends sfAction
{
   /*
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    $this->form = new sfForm;

    // Get repositories
    $criteria = new Criteria;
    $criteria->add(QubitOaiRepository::ID, null, Criteria::ISNOTNULL);
    $criteria->addAscendingOrderByColumn(QubitOaiRepository::NAME);
    $this->repositories = QubitOaiRepository::get($criteria);

    // Add URI field
    $this->form->setValidator('uri', new sfValidatorUrl(array('required' => true)));
    $this->form->setWidget('uri', new sfWidgetFormInputText);

    $this->harvestJob = array();

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters());
      if ($this->form->isValid())
      {
        $this->processForm();
      }
    }
  }

  protected function processForm()
  {
    if (0 < count(QubitOaiRepository::getByURI($this->form->getValue('uri'))))
    {
       $this->request->setAttribute('preExistingRepository', true);
       $this->forward('qtOaiPlugin', 'harvesterNewRepository');
    }

    $oaiSimple = simplexml_load_file("{$this->form->getValue('uri')}?verb=Identify");
    libxml_use_internal_errors(true);

    if ($oaiSimple)
    {
      $repository = new QubitOaiRepository();
      $Identify = $oaiSimple->Identify;

      $repository->setName($Identify->repositoryName);
      $repository->setUri($this->form->getValue('uri'));
      $repository->setAdminEmail($Identify->adminEmail);
      $repository->setEarliestTimestamp($Identify->earliestDatestamp);
      $repository->save();

      $harvest = new QubitOaiHarvest();
      $harvest->setOaiRepository($repository);
      $harvest->setMetadataPrefix('oai_dc');
      $harvest->save();
      $this->redirect(array('module' => 'qtOaiPlugin', 'action' => 'harvesterNewRepository'));
    }
    else
    {
      $this->request->setAttribute('parsingErrors', true);
      $this->forward('qtOaiPlugin', 'harvesterNewRepository');
    }
  }
}
