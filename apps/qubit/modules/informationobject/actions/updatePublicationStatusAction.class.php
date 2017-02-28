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

class InformationObjectUpdatePublicationStatusAction extends DefaultEditAction
{
  // Arrays not allowed in class constants
  public static
    $NAMES = array(
      'publicationStatus',
      'updateDescendants');

  protected function earlyExecute()
  {
    $this->resource = $this->getRoute()->resource;

    // Check that object exists and that it is not the root
    if (!isset($this->resource) || !isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'publish'))
    {
      QubitAcl::forwardUnauthorized();
    }
  }

  protected function addField($name)
  {
    switch ($name)
    {
      case 'publicationStatus':
        $publicationStatus = $this->resource->getStatus(array('typeId' => QubitTerm::STATUS_TYPE_PUBLICATION_ID));
        if (isset($publicationStatus))
        {
          $this->form->setDefault('publicationStatus', $publicationStatus->statusId);
        }
        else
        {
          $this->form->setDefault('publicationStatus', sfConfig::get('app_defaultPubStatus'));
        }

        $this->form->setValidator('publicationStatus', new sfValidatorString);

        $choices = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::PUBLICATION_STATUS_ID) as $item)
        {
          $choices[$item->id] = $item;
        }

        $this->form->setWidget('publicationStatus', new sfWidgetFormSelect(array('choices' => $choices)));

        break;

      case 'updateDescendants':
        $this->form->setValidator('updateDescendants', new sfValidatorBoolean);
        $this->form->setWidget('updateDescendants', new sfWidgetFormInputCheckbox);

        break;
    }
  }

  public function execute($request)
  {
    parent::execute($request);

    if ($this->request->getMethod() == 'POST')
    {
      $this->form->bind($request->getPostParameters());

      if ($this->form->isValid())
      { 
        // Update resource synchronously
        $publicationStatusId = $this->form->getValue('publicationStatus');
        $this->resource->setPublicationStatus($publicationStatusId);
        $this->resource->save();
        
        // Update descendants using job scheduler
        if (filter_var($this->form->getValue('updateDescendants'), FILTER_VALIDATE_BOOLEAN))
        {
          $options = array('objectId' => $this->resource->id, 'publicationStatusId' => $publicationStatusId);
          QubitJob::runJob('arUpdatePublicationStatusJob', $options);
      
          // Let user know descendants update has started
          $i18n = $this->context->i18n;
          $this->context->getConfiguration()->loadHelpers(array('Url'));
          $jobsUrl = url_for(array('module' => 'jobs', 'action' => 'browse'));
          $message = $i18n->__('Your description has been updated. Lower level descriptions are being updated now – check the <a href="%1">job scheduler page</a> for status and details.', array('%1' => $jobsUrl));
          $this->getUser()->setFlash('notice', $message);
        }

        // Create or delete DC and EAD XML exports
        $this->resource->updateXmlExports();

        $this->redirect(array($this->resource, 'module' => 'informationobject'));
      }
    }
  }
}
