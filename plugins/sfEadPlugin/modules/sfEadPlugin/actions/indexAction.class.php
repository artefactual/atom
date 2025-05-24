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
 * Render resource in EAD XML format.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 */
class sfEadPluginIndexAction extends InformationObjectIndexAction
{
    public function execute($request)
    {
        sfConfig::set('sf_escaping_strategy', false);

        // run the core informationObject show action commands
        parent::execute($request);

        $this->ead = new sfEadPlugin(
            $this->resource,
            ['public' => !$this->context->user->isAuthenticated()]
        );

        // Variables for hidden elements
        $this->authenticated = $this->getUser()->isAuthenticated();
        $this->defTemplate = sfConfig::get('app_default_template_informationobject');

        // Determine language(s) used in the export
        $this->exportLanguage = sfContext::getInstance()->user->getCulture();
        $this->sourceLanguage = $this->resource->getSourceCulture();

        // Instantiate Object to use in Converting ISO 639-1 language codes to 639-2
        $this->iso639convertor = new fbISO639_Map();

        // Set array with valid EAD level values (see ead.dtd line 2220)
        $this->eadLevels = ['class', 'collection', 'file', 'fonds', 'item', 'otherlevel', 'recordgrp', 'series', 'subfonds', 'subgrp', 'subseries'];
        $this->options = ['current-level-only' => false];
    }
}
