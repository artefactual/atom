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

class ObjectExportAction extends sfAction
{
  // export an object w/relations as an XML document with selected schema
  public function execute($request)
  {
    $this->baseObject = QubitObject::getById($request->id);

    // Check that object exists and that it is not the root
    if (!isset($this->baseObject) || !isset($this->baseObject->parent))
    {
      $this->forward404();
    }

    $this->forward404Unless($this->request->format);

    // load the export config for this schema if it exists
    $exportConfig = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.'object'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'export'.DIRECTORY_SEPARATOR.$this->request->format.'.yml';
    if (file_exists($exportConfig))
    {
      $this->schemaConfig = sfYaml::load($exportConfig);
    }

    // set the XML template to use for export
    if (!empty($this->schemaConfig['templateXML']))
    {
      $this->setTemplate($this->schemaConfig['templateXML']);
    }
    else
    {
      $this->setTemplate($this->request->format);
    }

    $request->setRequestFormat('xml');

    if (!extension_loaded('xsl'))
    {
      return;
    }

    // Copied from sfExecutionFilter::executeView()
    $view = $this->getController()->getView($this->moduleName, $this->actionName, sfView::SUCCESS);
    $view->getAttributeHolder()->add($this->varHolder->getAll());

    // create a new DOM document to export
    $this->exportDOM = new DOMDocument('1,0', 'UTF-8');
    $this->exportDOM->encoding = 'UTF-8';
    $this->exportDOM->formatOutput = true;
    $this->exportDOM->preserveWhiteSpace = false;
    $this->exportDOM->loadXML($view->render());

    // if no XSLs are specified, use the default one
    if (empty($this->schemaConfig['processXSLT']))
    {
      $this->schemaConfig = array('processXSLT' => array('export-postprocess.xsl'));
    }
    else
    {
      // little bit of fault-tolerance in case the filter has a string
      $this->schemaConfig['processXSLT'] = (array) $this->schemaConfig['processXSLT'];
      $this->schemaConfig['processXSLT'][] = 'export-postprocess.xsl';
    }

    // post-filter through XSLs in order
    foreach ($this->schemaConfig['processXSLT'] as $exportXSL)
    {
      $exportXSL = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'xslt'.DIRECTORY_SEPARATOR.$exportXSL;

      if (file_exists($exportXSL))
      {
        // instantiate an XSLT parser
        $xslDOM = new DOMDocument;
        $xslDOM->load($exportXSL);

        // Configure the transformer
        $xsltProc = new XSLTProcessor;
        $xsltProc->registerPHPFunctions();
        $xsltProc->importStyleSheet($xslDOM);

        // if we have a doctype declared, copy the values to preserve them in the XSL
        if (!empty($this->exportDOM->doctype))
        {
          $xsltProc->setParameter('', 'doctype', '!DOCTYPE '.$this->exportDOM->doctype->name.' PUBLIC "'.$this->exportDOM->doctype->publicId.'" "'.$this->exportDOM->doctype->systemId.'"');
        }

        $this->exportDOM->loadXML($xsltProc->transformToXML($this->exportDOM));
      }
    }

    // send final XML out to the browser
    return $this->renderText($this->exportDOM->saveXML());
  }
}
