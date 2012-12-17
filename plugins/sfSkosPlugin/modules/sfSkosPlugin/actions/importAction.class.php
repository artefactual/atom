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

class sfSkosPluginImportAction extends sfAction
{
  public function execute($request)
  {
    $this->form = new sfForm;

    $this->timer = new QubitTimer;
    $this->terms = array();
    $this->termsPerPage = intval(sfConfig::get('app_hits_per_page'));

    $this->taxonomy = null;
    $this->parent = QubitTerm::getById(QubitTerm::ROOT_ID);

    if (isset($this->getRoute()->resource))
    {
      $resource = $this->getRoute()->resource;

      if ('QubitTaxonomy' == $resource->className)
      {
        $this->taxonomy = QubitTaxonomy::getById($resource->id);
      }
      else
      {
        $this->parent = QubitTerm::getById($resource->id);
        $this->taxonomy = $this->parent->taxonomy;
      }
    }

    if (!isset($this->taxonomy))
    {
      $this->forward404();
    }

    // Check user authorization
    if (!QubitAcl::check($this->parent, 'create'))
    {
      QubitAcl::forwardUnauthorized();
    }

    $this->form->setWidget('file', new sfWidgetFormInputFile);
    $this->form->setValidator('file', new sfValidatorFile);

    if ($request->isMethod('post'))
    {
      $this->form->bind($request->getPostParameters(), $request->getFiles());

      if ($this->form->isValid())
      {
        if (null !== $file = $this->form->getValue('file'))
        {
          $doc = new domDocument();
          $doc->substituteEntities = true;
          $doc->load($file->getTempName());

          $this->skos = sfSkosPlugin::parse($doc, array('taxonomy' => $this->taxonomy, 'parent' => $this->parent));

        }
      }
    }
    else
    {
      $this->setTemplate('importSelect');
    }
  }
}
