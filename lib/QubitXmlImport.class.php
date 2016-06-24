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
 * Import an XML document into Qubit.
 *
 * @package    AccesstoMemory
 * @subpackage library
 * @author     MJ Suhonos <mj@suhonos.ca>
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Mike Cantelon <mike@artefactual.com>
 */
class QubitXmlImport
{
  protected
    $errors = null,
    $rootObject = null,
    $parent = null,
    $events = array();

  public function import($xmlFile, $options = array())
  {
    // load the XML document into a DOMXML object
    $importDOM = $this->loadXML($xmlFile, $options);

    // if we were unable to parse the XML file at all
    if (empty($importDOM->documentElement))
    {
      $errorMsg = sfContext::getInstance()->i18n->__('Unable to parse XML file: malformed or unresolvable entities');

      throw new Exception($errorMsg);
    }

    // if libxml threw errors, populate them to show in the template
    if ($importDOM->libxmlerrors)
    {
      // warning condition, XML file has errors (perhaps not well-formed or invalid?)
      foreach ($importDOM->libxmlerrors as $libxmlerror)
      {
        $xmlerrors[] = sfContext::getInstance()->i18n->__('libxml error %code% on line %line% in input file: %message%', array('%code%' => $libxmlerror->code, '%message%' => $libxmlerror->message, '%line%' => $libxmlerror->line));
      }

      $this->errors = array_merge((array) $this->errors, $xmlerrors);
    }

    $this->stripComments($importDOM);

    // Add local XML catalog for EAD DTD and DC and MODS XSD validations
    putenv('XML_CATALOG_FILES='.sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'xml'.DIRECTORY_SEPARATOR.'catalog.xml');

    if ('mods' == $importDOM->documentElement->tagName)
    {
      // XSD validation for MODS
      $schema = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'xsd'.DIRECTORY_SEPARATOR.'mods.xsd';

      if (!$importDOM->schemaValidate($schema))
      {
        $this->errors[] = 'XSD validation failed';
      }

      // Populate errors to show in the template
      foreach (libxml_get_errors() as $libxmlerror)
      {
        $this->errors[] = sfContext::getInstance()->i18n->__('libxml error %code% on line %line% in input file: %message%', array('%code%' => $libxmlerror->code, '%message%' => $libxmlerror->message, '%line%' => $libxmlerror->line));
      }

      $parser = new sfModsConvertor();
      if ($parser->parse($xmlFile))
      {
        $this->rootObject = $parser->getResource();
      }
      else
      {
        $errorData = $parser->getErrorData();
        $this->errors[] = array(sfContext::getInstance()->i18n->__('SAX xml parse error %code% on line %line% in input file: %message%', array('%code%' => $errorData['code'], '%message%' => $errorData['string'], '%line%' => $errorData['line'])));
      }

      return $this;
    }

    if ('eac-cpf' == $importDOM->documentElement->tagName)
    {
      $this->rootObject = new QubitActor;
      $this->rootObject->parentId = QubitActor::ROOT_ID;

      $eac = new sfEacPlugin($this->rootObject);
      $eac->parse($importDOM);

      $this->rootObject->save();

      if (isset($eac->itemsSubjectOf))
      {
        foreach ($eac->itemsSubjectOf as $item)
        {
          $relation = new QubitRelation;
          $relation->object = $this->rootObject;
          $relation->typeId = QubitTerm::NAME_ACCESS_POINT_ID;

          $item->relationsRelatedBysubjectId[] = $relation;
          $item->save();
        }
      }

      return $this;
    }

    // FIXME hardcoded until we decide how these will be developed
    $validSchemas = array(
      // document type declarations
      '+//ISBN 1-931666-00-8//DTD ead.dtd Encoded Archival Description (EAD) Version 2002//EN' => 'ead',
      '-//Society of American Archivists//DTD ead.dtd (Encoded Archival Description (EAD) Version 1.0)//EN' => 'ead1',
      // namespaces
      'http://www.loc.gov/METS/' => 'mets',
      'http://www.loc.gov/mods/' => 'mods',
      'http://www.loc.gov/MARC21/slim' => 'marc',
      // root element names
      //'collection' => 'marc',
      //'record' => 'marc',
      'record' => 'oai_dc_record',
      'dc' => 'dc',
      'oai_dc:dc' => 'dc',
      'dublinCore' => 'dc',
      'metadata' => 'dc',
      //'mets' => 'mets',
      'mods' => 'mods',
      'ead' => 'ead',
      'add' => 'alouette',
      'http://www.w3.org/2004/02/skos/core#' => 'skos'
    );

    // determine what kind of schema we're trying to import
    $schemaDescriptors = array($importDOM->documentElement->tagName);
    if (!empty($importDOM->namespaces))
    {
      krsort($importDOM->namespaces);
      $schemaDescriptors = array_merge($schemaDescriptors, $importDOM->namespaces);
    }
    if (!empty($importDOM->doctype))
    {
      $schemaDescriptors = array_merge($schemaDescriptors, array($importDOM->doctype->name, $importDOM->doctype->systemId, $importDOM->doctype->publicId));
    }

    foreach ($schemaDescriptors as $descriptor)
    {
      if (array_key_exists($descriptor, $validSchemas))
      {
        $importSchema = $validSchemas[$descriptor];

        // Store the used descriptor to differentiate between
        // oai_dc:dc and simple dc in XSD validation
        $usedDescriptor = $descriptor;
      }
    }

    switch ($importSchema)
    {
      case 'ead':

        // just validate EAD import for now until we can get StrictXMLParsing working for all schemas in the self::LoadXML function. Having problems right now loading schemas.
        $importDOM->validate();

        // if libxml threw errors, populate them to show in the template
        foreach (libxml_get_errors() as $libxmlerror)
        {
          $this->errors[] = sfContext::getInstance()->i18n->__('libxml error %code% on line %line% in input file: %message%', array('%code%' => $libxmlerror->code, '%message%' => $libxmlerror->message, '%line%' => $libxmlerror->line));
        }

        break;

      case 'dc':

        // XSD validation for DC
        $schema = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'xsd'.DIRECTORY_SEPARATOR;
        if ($usedDescriptor == 'oai_dc:dc')
        {
          $schema .= 'oai_dc.xsd';
        }
        else
        {
          $schema .= 'simpledc20021212.xsd';
        }

        if (!$importDOM->schemaValidate($schema))
        {
          $this->errors[] = 'XSD validation failed';
        }

        // Populate errors to show in the template
        foreach (libxml_get_errors() as $libxmlerror)
        {
          $this->errors[] = sfContext::getInstance()->i18n->__('libxml error %code% on line %line% in input file: %message%', array('%code%' => $libxmlerror->code, '%message%' => $libxmlerror->message, '%line%' => $libxmlerror->line));
        }

        break;

      case 'skos':

        $criteria = new Criteria;
        $criteria->add(QubitSetting::NAME, 'plugins');
        $setting = QubitSetting::getOne($criteria);
        if (null === $setting || !in_array('sfSkosPlugin', unserialize($setting->getValue(array('sourceCulture' => true)))))
        {
          throw new sfException(sfContext::getInstance()->i18n->__('The SKOS plugin is not enabled'));
        }

        $importTerms = sfSkosPlugin::parse($importDOM, $options);
        $this->rootObject = QubitTaxonomy::getById(QubitTaxonomy::SUBJECT_ID);
        $this->count = count($importTerms);

        return $this;

        break;
    }

    $importMap = sfConfig::get('sf_app_module_dir').DIRECTORY_SEPARATOR.'object'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'import'.DIRECTORY_SEPARATOR.$importSchema.'.yml';
    if (!file_exists($importMap))
    {
      // error condition, unknown schema or no import filter
      $errorMsg = sfContext::getInstance()->i18n->__('Unknown schema or import format: "%format%"', array('%format%' => $importSchema));

      throw new Exception($errorMsg);
    }

    $this->schemaMap = sfYaml::load($importMap);

    // if XSLs are specified in the mapping, process them
    if (!empty($this->schemaMap['processXSLT']))
    {
      // pre-filter through XSLs in order
      foreach ((array) $this->schemaMap['processXSLT'] as $importXSL)
      {
        $importXSL = sfConfig::get('sf_data_dir').DIRECTORY_SEPARATOR.'xslt'.DIRECTORY_SEPARATOR.$importXSL;

        if (file_exists($importXSL))
        {
          // instantiate an XSLT parser
          $xslDOM = new DOMDocument;
          $xslDOM->load($importXSL);

          // Configure the transformer
          $xsltProc = new XSLTProcessor;
          $xsltProc->registerPHPFunctions();
          $xsltProc->importStyleSheet($xslDOM);

          $importDOM->loadXML($xsltProc->transformToXML($importDOM));
          unset($xslDOM);
          unset($xsltProc);
        }
        else
        {
          $this->errors[] = sfContext::getInstance()->i18n->__('Unable to load import XSL filter: "%importXSL%"', array('%importXSL%' => $importXSL));
        }
      }

      // re-initialize xpath on the new XML
      $importDOM->xpath = new DOMXPath($importDOM);
    }

    // switch source culture if language is set in an EAD document
    if ($importSchema == 'ead')
    {
      if (is_object($langusage = $importDOM->xpath->query('//eadheader/profiledesc/langusage/language/@langcode')))
      {
        $sf_user = sfContext::getInstance()->user;
        $currentCulture = $sf_user->getCulture();
        $langCodeConvertor = new fbISO639_Map;
        foreach ($langusage as $language)
        {
          $isocode = trim(preg_replace('/[\n\r\s]+/', ' ', $language->nodeValue));
          // convert to Symfony culture code
          if (!$twoCharCode = strtolower($langCodeConvertor->getID1($isocode, false)))
          {
            $twoCharCode = $isocode;
          }
          // Check to make sure that the selected language is supported with a Symfony i18n data file.
          // If not it will cause a fatal error in the Language List component on every response.

          ProjectConfiguration::getActive()->loadHelpers('I18N');

          try
          {
            format_language($twoCharCode, $twoCharCode);
          }
          catch (Exception $e)
          {
            $this->errors[] = sfContext::getInstance()->i18n->__('EAD "langmaterial" is set to').': "'.$isocode.'". '.sfContext::getInstance()->i18n->__('This language is currently not supported.');
            continue;
          }

          if ($currentCulture !== $twoCharCode)
          {
            $this->errors[] = sfContext::getInstance()->i18n->__('EAD "langmaterial" is set to').': "'.$isocode.'" ('.format_language($twoCharCode, 'en').'). '.sfContext::getInstance()->i18n->__('Your XML document has been saved in this language and your user interface has just been switched to this language.');
          }
          $sf_user->setCulture($twoCharCode);
          // can only set to one language, so have to break once the first valid language is encountered
          break;
        }
      }
    }

    unset($this->schemaMap['processXSLT']);

    // go through schema map and populate objects/properties
    foreach ($this->schemaMap as $name => $mapping)
    {
      // if object is not defined or a valid class, we can't process this mapping
      if (empty($mapping['Object']) || !class_exists('Qubit'.$mapping['Object']))
      {
        $this->errors[] = sfContext::getInstance()->i18n->__('Non-existent class defined in import mapping: "%class%"', array('%class%' => 'Qubit'.$mapping['Object']));
        continue;
      }

      // get a list of XML nodes to process
      $nodeList = $importDOM->xpath->query($mapping['XPath']);

      foreach ($nodeList as $domNode)
      {
        // create a new object
        $class = 'Qubit'.$mapping['Object'];
        $currentObject = new $class;

        // set the rootObject to use for initial display in successful import
        if (!$this->rootObject)
        {
          $this->rootObject = $currentObject;
        }

        // use DOM to populate object
        $this->populateObject($domNode, $importDOM, $mapping, $currentObject, $importSchema);
      }
    }

    return $this;
  }

  private function populateObject(&$domNode, &$importDOM, &$mapping, &$currentObject, $importSchema)
  {
    // if a parent path is specified, try to parent the node
    if (empty($mapping['Parent']))
    {
      $parentNodes = new DOMNodeList;
    }
    else
    {
      $parentNodes = $importDOM->xpath->query('('.$mapping['Parent'].')', $domNode);
    }

    if ($parentNodes->length > 0)
    {
      // parent ID comes from last node in the list because XPath forces forward document order
      $parentId = $parentNodes->item($parentNodes->length - 1)->getAttribute('xml:id');
      unset($parentNodes);

      if (!empty($parentId) && is_callable(array($currentObject, 'setParentId')))
      {
        $currentObject->parentId = $parentId;
      }
    }
    else
    {
      // orphaned object, set root if possible
      if (isset($this->parent))
      {
        $currentObject->parentId = $this->parent->id;
      }
      else if (is_callable(array($currentObject, 'setRoot')))
      {
        $currentObject->setRoot();
      }
    }

    // go through methods and populate properties
    $this->processMethods($domNode, $importDOM, $mapping['Methods'], $currentObject, $importSchema);

    // make sure we have a publication status set before indexing
    if ($currentObject instanceof QubitInformationObject && count($currentObject->statuss) == 0)
    {
      $currentObject->setPublicationStatus(sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID));
    }

    // save the object after it's fully-populated
    $currentObject->save();

    // write the ID onto the current XML node for tracking
    $domNode->setAttribute('xml:id', $currentObject->id);
  }

  /*
   * Cycle through methods and populate object based on relevant data
   *
   * @return  null
   */
  private function processMethods(&$domNode, &$importDOM, $methods, &$currentObject, $importSchema)
  {
    // We want to keep track of nodes processed so we don't process one twice
    // if multiple selectors apply to it (for example the generic "odd" tag
    // handler should not trigger if a specific "odd" handler was previously
    // triggered for the same node)
    $processed = array();

    // go through methods and populate properties
    foreach ($methods as $name => $methodMap)
    {
      // if method is not defined, we can't process this mapping
      if (empty($methodMap['Method']) || !is_callable(array($currentObject, $methodMap['Method'])))
      {
        $this->errors[] = sfContext::getInstance()->i18n->__('Non-existent method defined in import mapping: "%method%"', array('%method%' => $methodMap['Method']));
        continue;
      }

      // Get a list of XML nodes to process
      // This condition mitigates a problem where the XPath query wasn't working
      // as expected, see #4302 for more details
      if ($importSchema == 'dc' && $methodMap['XPath'] != '.')
      {
        $nodeList2 = $importDOM->getElementsByTagName($methodMap['XPath']);
      }
      else
      {
        $nodeList2 = $importDOM->xpath->query($methodMap['XPath'], $domNode);
      }

      if (is_object($nodeList2))
      {
        switch($name)
        {
          // hack: some multi-value elements (e.g. 'languages') need to get passed as one array instead of individual nodes values
          case 'languages':
          case 'language':
            $langCodeConvertor = new fbISO639_Map;
            $isID3 = ($importSchhema == 'dc') ? true : false;

            $value = array();
            foreach ($nodeList2 as $item)
            {
              if ($twoCharCode = $langCodeConvertor->getID1($item->nodeValue, $isID3))
              {
                $value[] = strtolower($twoCharCode);
              }
              else
              {
                $value[] = $item->nodeValue;
              }
            }
            $currentObject->language = $value;

            break;

          case 'flocat':
          case 'digital_object':
            $resources = array();
            foreach ($nodeList2 as $item)
            {
              $resources[] = $item->nodeValue;
            }

            if (0 < count($resources))
            {
              $currentObject->importDigitalObjectFromUri($resources, $this->errors);
            }

            break;

          case 'container':
            foreach ($nodeList2 as $item)
            {
              $container = $item->nodeValue;
              $type = $importDOM->xpath->query('@type', $item)->item(0)->nodeValue;
              $label = $importDOM->xpath->query('@label', $item)->item(0)->nodeValue;
              $parent = $importDOM->xpath->query('@parent', $item)->item(0)->nodeValue;
              $location = $importDOM->xpath->query('did/physloc[@id="'.$parent.'"]', $domNode)->item(0)->nodeValue;

              $currentObject->importPhysicalObject($location, $container, $type, $label);
            }

            break;

          case 'relatedunitsofdescription':
            $i = 0;
            $nodeValue = '';
            foreach ($nodeList2 as $item)
            {
              if ($i++ == 0)
              {
                $nodeValue .= self::normalizeNodeValue($item);
              }
              else
              {
                $nodeValue .= "\n\n" . self::normalizeNodeValue($item);
              }
            }

            $currentObject->setRelatedUnitsOfDescription($nodeValue);

            break;

          default:
            foreach ($nodeList2 as $key => $domNode2)
            {
              // Skip this node if method path isn't "self" and node's previously been processed
              if ($methodMap['XPath'] != '.' && isset($processed[$domNode2->getNodePath()]))
              {
                continue;
              }

              // Take note that this node has been processed
              $processed[$domNode2->getNodePath()] = true;

              // normalize the node text; NB: this will strip any child elements, eg. HTML tags
              $nodeValue = self::normalizeNodeValue($domNode2);

              // if you want the full XML from the node, use this
              $nodeXML = $domNode2->ownerDocument->saveXML($domNode2);
              // set the parameters for the method call
              if (empty($methodMap['Parameters']))
              {
                $parameters = array($nodeValue);
              }
              else
              {
                $parameters = array();
                foreach ((array) $methodMap['Parameters'] as $parameter)
                {
                  // if the parameter begins with %, evaluate it as an XPath expression relative to the current node
                  if ('%' == substr($parameter, 0, 1))
                  {
                    // evaluate the XPath expression
                    $xPath = substr($parameter, 1);
                    $result = $importDOM->xpath->query($xPath, $domNode2);

                    if ($result->length > 1)
                    {
                      // convert nodelist into an array
                      foreach ($result as $element)
                      {
                        $resultArray[] = $element->nodeValue;
                      }
                      $parameters[] = $resultArray;
                    }
                    else
                    {
                      // pass the node value unaltered; this provides an alternative to $nodeValue above
                      $parameters[] = $result->item(0)->nodeValue;
                    }
                  }
                  else
                  {
                    // Confirm DOMXML node exists to avoid warnings at run-time
                    if (false !== preg_match_all('/\$importDOM->xpath->query\(\'@\w+\', \$domNode2\)->item\(0\)->nodeValue/', $parameter, $matches))
                    {
                      foreach ($matches[0] as $match)
                      {
                        $str = str_replace('->nodeValue', '', $match);

                        if (null !== ($node = eval('return '.$str.';')))
                        {
                          // Substitute node value for search string
                          $parameter = str_replace($match, '\''.$node->nodeValue.'\'', $parameter);
                        }
                        else
                        {
                          // Replace empty nodes with null in parameter string
                          $parameter = str_replace($match, 'null', $parameter);
                        }
                      }
                    }

                    eval('$parameters[] = '.$parameter.';');
                  }
                }
              }

              // Load taxonomies into variables to avoid use of magic numbers
              $termData = QubitFlatfileImport::loadTermsFromTaxonomies(array(
                QubitTaxonomy::NOTE_TYPE_ID      => 'noteTypes',
                QubitTaxonomy::RAD_NOTE_ID       => 'radNoteTypes',
                QubitTaxonomy::RAD_TITLE_NOTE_ID => 'titleNoteTypes',
                QubitTaxonomy::DACS_NOTE_ID      => 'dacsSpecializedNotesTypes'
              ));

              $titleVariationNoteTypeId            = array_search('Variations in title', $termData['titleNoteTypes']['en']);
              $titleAttributionsNoteTypeId         = array_search('Attributions and conjectures', $termData['titleNoteTypes']['en']);
              $titleContinuationNoteTypeId         = array_search('Continuation of title', $termData['titleNoteTypes']['en']);
              $titleStatRepNoteTypeId              = array_search('Statements of responsibility', $termData['titleNoteTypes']['en']);
              $titleParallelNoteTypeId             = array_search('Parallel titles and other title information', $termData['titleNoteTypes']['en']);
              $titleSourceNoteTypeId               = array_search('Source of title proper', $termData['titleNoteTypes']['en']);
              $alphaNumericaDesignationsNoteTypeId = array_search('Alpha-numeric designations', $termData['radNoteTypes']['en']);
              $physDescNoteTypeId                  = array_search('Physical description', $termData['radNoteTypes']['en']);
              $editionNoteTypeId                   = array_search('Edition', $termData['radNoteTypes']['en']);
              $conservationNoteTypeId              = array_search('Conservation', $termData['radNoteTypes']['en']);

              $pubSeriesNoteTypeId                 = array_search("Publisher's series", $termData['radNoteTypes']['en']);
              $rightsNoteTypeId                    = array_search("Rights", $termData['radNoteTypes']['en']);
              $materialNoteTypeId                  = array_search("Accompanying material", $termData['radNoteTypes']['en']);
              $generalNoteTypeId                   = array_search("General note", $termData['noteTypes']['en']);

              $dacsAlphaNumericaDesignationsNoteTypeId  = array_search('Alphanumeric designations', $termData['dacsSpecializedNotesTypes']['en']);
              $dacsCitationNoteTypeId            = array_search("Citation", $termData['dacsSpecializedNotesTypes']['en']);
              $dacsConservationNoteTypeId        = array_search("Conservation", $termData['dacsSpecializedNotesTypes']['en']);
              $dacsProcessingInformationNoteTypeId   = array_search("Processing information", $termData['dacsSpecializedNotesTypes']['en']);
              $dacsVariantTitleInformationNoteTypeId   = array_search("Variant title information", $termData['dacsSpecializedNotesTypes']['en']);

              // invoke the object and method defined in the schema map
              $obj = call_user_func_array(array( & $currentObject, $methodMap['Method']), $parameters);

              // If an actor/event object was returned, track that
              // in the events cache for later cleanup
              if(!empty($obj))
              {
                $this->trackEvent($obj, $domNode2);
              }
            }
        }

        $this->associateEvents();

        unset($nodeList2);
      }
    }
  }

  /**
   * modified helper methods from (http://www.php.net/manual/en/ref.dom.php):
   *
   * - create a DOMDocument from a file
   * - parse the namespaces in it
   * - create a XPath object with all the namespaces registered
   *  - load the schema locations
   *  - validate the file on the main schema (the one without prefix)
   *
   * @param string $xmlFile XML document file
   * @param array $options optional parameters
   * @return DOMDocument an object representation of the XML document
   */
  protected function loadXML($xmlFile, $options = array())
  {
    libxml_use_internal_errors(true);

    // FIXME: trap possible load validation errors (just suppress for now)
    $err_level = error_reporting(0);
    $doc = new DOMDocument('1.0', 'UTF-8');

    // Default $strictXmlParsing to false
    $strictXmlParsing = (isset($options['strictXmlParsing'])) ? $options['strictXmlParsing'] : false;

    // Pre-fetch the raw XML string from file so we can remove any default
    // namespaces and reuse the string for later when finding/registering namespaces.
    $rawXML = file_get_contents($xmlFile);

    if ($strictXmlParsing)
    {
      // enforce all XML parsing rules and validation
      $doc->validateOnParse = true;
      $doc->resolveExternals = true;
    }
    else
    {
      // try to load whatever we've got, even if it's malformed or invalid
      $doc->recover = true;
      $doc->strictErrorChecking = false;
    }
    $doc->formatOutput = false;
    $doc->preserveWhitespace = false;
    $doc->substituteEntities = true;

    $doc->loadXML($this->removeDefaultNamespace($rawXML));

    $xsi = false;
    $doc->namespaces = array();
    $doc->xpath = new DOMXPath($doc);

    // pass along any XML errors that have been generated
    $doc->libxmlerrors = libxml_get_errors();

    // if the document didn't parse correctly, stop right here
    if (empty($doc->documentElement))
    {
      return $doc;
    }

    error_reporting($err_level);

    // look through the entire document for namespaces
    // FIXME: #2787
    // https://projects.artefactual.com/issues/2787
    //
    // THIS SHOULD ONLY INSPECT THE ROOT NODE NAMESPACES
    // Consider: http://www.php.net/manual/en/book.dom.php#73793

    $re = '/xmlns:([^=]+)="([^"]+)"/';
    preg_match_all($re, $rawXML, $mat, PREG_SET_ORDER);

    foreach ($mat as $xmlns)
    {
      $pre = $xmlns[1];
      $uri = $xmlns[2];

      $doc->namespaces[$pre] = $uri;

      if ($pre == '')
      {
        $pre = 'noname';
      }
      $doc->xpath->registerNamespace($pre, $uri);
    }

/*
    if (!isset($doc->namespaces['']))
    {
      $doc->namespaces[''] = $doc->documentElement->lookupnamespaceURI(null);
    }

    if ($xsi)
    {
      $doc->schemaLocations = array();
      $lst = $doc->xpath->query('//@$xsi:schemaLocation');
      foreach ($lst as $el)
      {
        $re = "{[\\s\n\r]*([^\\s\n\r]+)[\\s\n\r]*([^\\s\n\r]+)}";
        preg_match_all($re, $el->nodeValue, $mat);
        for ($i = 0; $i < count($mat[0]); $i++)
        {
          $value = $mat[2][$i];
          $doc->schemaLocations[$mat[1][$i]] = $value;
        }
      }

      // validate document against default namespace schema
      $doc->schemaValidate($doc->schemaLocations[$doc->namespaces['']]);
    }
*/
    return $doc;
  }

  /**
   *
   *
   * @return DOMNodeList
   */
  public static function queryDomNode($node, $xpathQuery)
  {
    $doc = new DOMDocument();
    $doc->loadXML('<xml></xml>');
    $doc->documentElement->appendChild($doc->importNode($node, true));
    $xpath = new DOMXPath($doc);
    return $xpath->query($xpathQuery);
  }

  /**
   * Return true if import had errors
   *
   * @return boolean
   */
  public function hasErrors()
  {
    return $this->errors != null;
  }

  /**
   * Return array of error messages
   *
   * @return unknown
   */
  public function getErrors()
  {
    return $this->errors;
  }

  /**
   * Get the root object for the import
   *
   * @return mixed the root object (object type depends on import type)
   */
  public function getRootObject()
  {
    return $this->rootObject;
  }

  /**
   * Get the root object for the import
   *
   * @return mixed the root object (object type depends on import type)
   */
  public function setParent($parent)
  {
    return $this->parent = $parent;
  }

  /**
   * Replace </lb> tags for '\n'
   *
   * @return node value without linebreaks tags
   */
  public static function replaceLineBreaks($node)
  {
    $nodeValue = '';
    $fieldsArray = array('extent', 'physfacet', 'dimensions');

    foreach ($node->childNodes as $child)
    {
      if ($child->nodeName == 'lb')
      {
        $nodeValue .= "\n";
      }
      else if (in_array($child->tagName, $fieldsArray))
      {
        foreach ($child->childNodes as $childNode)
        {
          if ($childNode->nodeName == 'lb')
          {
            $nodeValue .= "\n";
          }
          else
          {
            $nodeValue .= preg_replace('/[\n\r\s]+/', ' ', $childNode->nodeValue);
          }
        }
      }
      else
      {
        $nodeValue .= preg_replace('/[\n\r\s]+/', ' ', $child->nodeValue);
      }
    }

    return $nodeValue;
  }

  /**
   * Make sure to remove any default namespaces from
   * EAD tags. See issue #7280 for details.
   */
  private function removeDefaultNamespace($xml)
  {
    return preg_replace('/(<ead.*?)xmlns="[^"]*"\s+(.*?>)/', '${1}${2}', $xml, 1);
  }

  /**
   * Remove all XML comments from the document.
   */
  private function stripComments($doc)
  {
    $xp = new DOMXPath($doc);
    $nodes = $xp->query('//comment()');

    for ($i = 0; $i < $nodes->length; $i++)
    {
      $nodes->item($i)->parentNode->removeChild($nodes->item($i));
    }
  }

  /**
   * Normalize node, replaces <p> and <lb/>
   *
   * @return node value normalized
   */
  public static function normalizeNodeValue($node)
  {
    $nodeValue = '';

    if (!($node instanceof DOMAttr))
    {
      $nodeList = $node->getElementsByTagName('p');

      if (0 < $nodeList->length)
      {
        $i = 0;
        foreach ($nodeList as $pNode)
        {
          if ($i++ == 0)
          {
            $nodeValue .= self::replaceLineBreaks($pNode);
          }
          else
          {
            $nodeValue .= "\n\n" . self::replaceLineBreaks($pNode);
          }
        }
      }
      else
      {
        $nodeValue .= self::replaceLineBreaks($node);
      }
    }
    else
    {
      $nodeValue .= $node->nodeValue;
    }

    return trim($nodeValue);
  }

  /**
   * Track objects to be reassociated with an event on import.
   * This is used to associate actors and places with events for
   * RAD-style events.
   */
  private function trackEvent($object, $node)
  {
    $kind = $node->nodeName;
    if ($kind === 'geogname')
    {
      $key = 'place';
    }
    else if ($kind === 'unitdate')
    {
      $key = 'event';
    }
    else if (in_array($kind, array('name', 'persname', 'corpname', 'famname')))
    {
      $key = 'actor';
    }
    else
    {
      return;
    }

    $id = $node->getAttribute('id');
    if (!empty($id))
    {
      // The ID value is suffixed with its category, e.g. 384_place
      // This is because `id` is required to be unique within the entire
      // document.
      //
      // First check if the ID is actually an AtoM tag, since the ID
      // may exist for another purpose.
      if (substr($id, 0, 4) == 'atom' && substr($id, -strlen($key)) == $key) {
        // chop off atom_ prefix and the _category suffix
        $id = substr($id, 5, -strlen($key) - 1);
        array_key_exists($id, $this->events) || $this->events[$id] = array();
        $this->events[$id][$key] = $object;
      }
    }
  }

  /**
   * Reattach all places and actors to their respective events,
   * using the $events map on this object.
   */
  private function associateEvents()
  {
    foreach ($this->events as $id => $values)
    {
      $event = $values['event'];

      if (!empty($event))
      {
        $place = array_key_exists('place', $values) ? $values['place'] : NULL;
        $actor = array_key_exists('actor', $values) ? $values['actor'] : NULL;

        if ($place !== NULL && $event->getPlace() === NULL)
        {
          $otr = new QubitObjectTermRelation;
          $otr->setObject($event);
          $otr->setTerm($place);
          $otr->save();
        }

        if ($actor !== NULL && $event->getActor() === NULL)
        {
          $event->setActor($actor);
        }

        $event->save();
      }
    }
  }
}
