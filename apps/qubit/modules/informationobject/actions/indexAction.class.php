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
 * Display an information object
 *
 * @package    AccesstoMemory
 * @subpackage information object
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectIndexAction extends sfAction
{
  protected function addField($validatorSchema, $name)
  {
    switch ($name)
    {
      case 'levelOfDescription':
        $forbiddenValues = array();
        foreach ($this->resource->ancestors->orderBy('rgt') as $item)
        {
          if (isset($item->levelOfDescription))
          {
            switch ($item->levelOfDescription->getName(array('sourceCulture' => true)))
            {
              case 'Item':
                $forbiddenValues[] = 'Item';

              case 'File':
                $forbiddenValues[] = 'File';

              case 'Sub-subseries':
                $forbiddenValues[] = 'Sub-subseries';

              case 'Subseries':
                $forbiddenValues[] = 'Subseries';

              case 'Series':
                $forbiddenValues[] = 'Series';

              case 'Sub-subfonds':
                $forbiddenValues[] = 'Sub-subfonds';

              case 'Subfonds':
                $forbiddenValues[] = 'Subfonds';

              case 'Fonds':

                // Collection may not be a descendant of fonds
                $forbiddenValues[] = 'Fonds';
                $forbiddenValues[] = 'Collection';

                break;

              case 'Collection':

                // Neither fonds nor subfonds may be descendants of collection
                $forbiddenValues[] = 'Subfonds';
                $forbiddenValues[] = 'Fonds';
                $forbiddenValues[] = 'Collection';

                break;
            }

            break;
          }
        }

        $validatorSchema->levelOfDescription = new sfValidatorBlacklist(array(
          'forbidden_values' => $forbiddenValues,
          'required' => true));

        break;
    }
  }

  public function execute($request)
  {
    $this->resource = $this->getRoute()->resource;

    // placed here rather than adding it five times to
    // sf{Dc,Mods,Rad,Isad,Dacs}Plugin's view.yml files
    $this->getResponse()->addStylesheet('fullWidthTreeView', 'last');
    $this->getResponse()->addStylesheet('/vendor/jstree/themes/default/style', 'last');
    $this->getResponse()->addJavascript('fullWidthTreeView', 'last');
    $this->getResponse()->addJavascript('/vendor/jstree/jstree.min.js', 'last');
    $this->getResponse()->addJavascript('history.js', 'last');

    // Check that this isn't the root
    if (!isset($this->resource->parent))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->resource, 'read'))
    {
      QubitAcl::forwardToSecureAction();
    }

    $this->dispatcher->notify(new sfEvent($this, 'access_log.view', array('object' => $this->resource)));

    if ('print' == $request->getGetParameter('media', 'screen'))
    {
      $this->getResponse()->addStylesheet('print-preview', 'last');
    }

    // Only show link to view/download master copy of digital object if the
    // user has readMaster permissions OR it's a text object (to allow reading)
    $this->digitalObjectLink = null;
    if (0 < count($this->resource->digitalObjects)
      && (QubitAcl::check($this->resource, 'readMaster')
        || in_array($this->resource->digitalObjects[0]->mediaTypeId, array(QubitTerm::TEXT_ID, QubitTerm::AUDIO_ID))))
    {
      if (QubitTerm::EXTERNAL_URI_ID == $this->resource->digitalObjects[0]->usageId)
      {
        $this->digitalObjectLink = $this->resource->digitalObjects[0]->path;
      }
      else
      {
        $this->digitalObjectLink = $request->getUriPrefix().$request->getRelativeUrlRoot().$this->resource->digitalObjects[0]->getFullPath();
      }
    }
  }
}
