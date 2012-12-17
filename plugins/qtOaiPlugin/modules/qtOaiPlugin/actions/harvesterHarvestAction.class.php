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
 * Harvest OAI-PMH records
 *
 * @package    AccesstoMemory
 * @subpackage oai
 * @author     Mathieu Fortin Library and Archives Canada <mathieu.fortin@lac-bac.gc.ca>
 */
class qtOaiPluginHarvesterHarvestAction extends sfAction
{
  /**
   * Executes action
   *
   * @param sfRequest $request A request object
   */
  public function execute($request)
  {
    //Set resumption token to null so that we enter in the harvester loop
    $resumptionToken = 1;

    //Set no records match to false to start with
    $this->noRecordsMatch = false;

    //Keep track of number of records harvested
    $this->recordCount = 0;

    //If the request did not go through the proper routing, forward to 404
    if (!isset($request->id))
    {
      $this->forward404();
    }

    $harvestInfo = QubitOaiHarvest::getById($request->id);
    $harvestInfo->setLastHarvestAttempt(QubitOai::getDate());
    $harvestInfo->save();
    $rep = $harvestInfo->getOaiRepository();

    //If repository was not found 404
    if (!$rep)
    {
      $this->forward404();
    }

    $this->repositoryName = $rep->getName();
    //Initialise $oaiSimpleRes

    $verb = '';
    $oaiSimpleRes = array();
    $from = date('Y-m-d\TH:i:s\Z', strtotime($harvestInfo->getLastHarvest()));
    $until = gmdate('Y-m-d\TH:i:s\Z');

    //Create the base request
    $verb = 'verb=ListRecords';
    if ($harvestInfo->getLastHarvest() != null)
    {
      $verb .= '&from='.$from;
    }
    $verb .= '&until='.$until;
    $verb .= '&metadataPrefix='.$harvestInfo->getMetadataPrefix();

    //Add the set parameter if supplied
    if ($harvestInfo->getSet() != null)
    {
      $verb .= '&set='.$harvestInfo->getSet();
    }

    while ($resumptionToken)
    {
      //Load XML through simplexml http
      $oaiSimple = simplexml_load_file($rep->getUri().'?'.$verb);

      //Strip oai header, construct array of records
      $oaiSimple->registerXPathNamespace('c', 'http://www.openarchives.org/OAI/2.0/');
      if ($oaiSimple->xpath('//c:error'))
      {
        $oaiReceivedError = $oaiSimple->xpath('//c:error');
        $oaiReceivedErrorAttr = $oaiReceivedError[0]->attributes();

        if ($oaiReceivedErrorAttr['code'] == 'noRecordsMatch')
        {
          $this->noRecordsMatch = true;
        } else
        {
          $this->forward404();
        }
      }

      if (!$this->noRecordsMatch)
      {
        //Container for xml import errors
        $this->errorsFound = array();
        $this->errorsXML = array();

        //Create header and footer for XML record for it to validate
        $oaiHeader = '<?xml version="1.0" encoding="UTF-8" ?>';

        $oaiFooter = '';
        $oaiRecords = $oaiSimple->xpath('//c:ListRecords/c:record');
        foreach ($oaiRecords as $oaiRec)
        {
          $oaiRec = $oaiHeader.$oaiRec->asXML().$oaiFooter."\n";

          $options = array();
          $options = $options['strictXmlParsing'] = false;
          $importer = new QubitXmlImport;
          $importer->import($oaiRec, $options);
//          $importer = QubitXmlImport::execute($oaiRec, $options);
          if ($importer->hasErrors())
          {
            $this->errorsFound[] = $importer->getErrors();
            $this->errorsXML[] = $oaiRec;
          }
        }

        // Increment record count to keep track of number of records harvested
        $this->recordCount += count($oaiRecords);

        $nbrErrors = count($this->errorsFound);
        $errorReport = '';
        $errorReportHTML = '';
        for ($i = 0; $i < $nbrErrors; $i++)
        {
          $errorReport .= "Error when importing record:\n\n".$this->errorsXML[$i]."\n\nError message:\n".$this->errorsFound[$i];
          $errorReport .= "\n**************************************************\n\n";
          $errorReportHTML .= "Error when importing record:\n\n <br>".$this->errorsXML[$i]."\n\n <br> Error message: <br> \n".$this->errorsFound[$i];
        }
      }

      //Check for resumption token which will also be the while loop indicator
      $oaiResumptionToken = $oaiSimple->xpath('//c:ListRecords/c:resumptionToken');
      if ($oaiResumptionToken == false || count($oaiResumptionToken) > 1)
      {
        $resumptionToken = false;
      } else
      {
        $resumptionToken = $oaiResumptionToken[0];
        $verb = 'verb=ListRecords&resumptionToken='.$resumptionToken;
      }
    }

    // Update last harvest date
    $harvestInfo->setLastHarvest($until);
    $harvestInfo->save();
  }
}
