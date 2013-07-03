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
 * SKOS representation of taxonomic data.
 *
 * @package    AccesstoMemory
 * @subpackage sfSkosPlugin
 * @author     David Juhasz <david@artefactual.com>
 */

class sfSkosPluginIndexAction extends sfAction
{
  public function execute($request)
  {
    $resource = $this->getRoute()->resource;

    if (!isset($resource))
    {
      $this->forward404();
    }

    if ('QubitTerm' == $resource->className)
    {
      $this->selectedTerm = QubitTerm::getById($resource->id);
      $this->terms = $this->selectedTerm->descendants->andSelf()->orderBy('lft');
      $this->taxonomy = $this->selectedTerm->taxonomy;
      $this->topLevelTerms = array($this->selectedTerm);
    }
    else
    {
      $this->terms = QubitTaxonomy::getTaxonomyTerms($resource->id);
      $this->taxonomy = QubitTaxonomy::getById($resource->id);
      $this->topLevelTerms = QubitTaxonomy::getTaxonomyTerms($resource->id, array('level' => 'top'));
    }

    $request->setRequestFormat('xml');
  }
}
