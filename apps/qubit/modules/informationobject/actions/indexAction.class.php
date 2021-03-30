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
 * Display an information object.
 *
 * @author     Peter Van Garderen <peter@artefactual.com>
 * @author     Jack Bates <jack@nottheoilrig.com>
 * @author     David Juhasz <david@artefactual.com>
 */
class InformationObjectIndexAction extends sfAction
{
    public function execute($request)
    {
        $this->resource = $this->getRoute()->resource;

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'read')) {
            QubitAcl::forwardToSecureAction();
        }

        $this->dispatcher->notify(new sfEvent($this, 'access_log.view', ['object' => $this->resource]));

        if ('fullWidth' == sfConfig::get('app_treeview_type__source', 'sidebar')) {
            $this->getResponse()->addStylesheet('fullWidthTreeView', 'last');
            $this->getResponse()->addStylesheet('/vendor/jstree/themes/default/style.min.css', 'last');
            $this->getResponse()->addJavascript('treeviewTypes', 'last');
            $this->getResponse()->addJavascript('pager', 'last');
            $this->getResponse()->addJavascript('treeViewPager', 'last');
            $this->getResponse()->addJavascript('fullWidthTreeView', 'last');
            $this->getResponse()->addJavascript('/vendor/jstree/jstree.min.js', 'last');
            $this->getResponse()->addJavaScript('/vendor/mediaelement/mediaelement-and-player.min.js', 'last');
            $this->getResponse()->addJavaScript('mediaelement', 'last');
            $this->getResponse()->addStyleSheet('/vendor/mediaelement/mediaelementplayer.min.css');
        }

        $scopeAndContent = $this->resource->getScopeAndContent(['cultureFallback' => true]);
        if (!empty($scopeAndContent)) {
            $this->getContext()->getConfiguration()->loadHelpers(['Text', 'Qubit']);
            $this->response->addMeta('description', truncate_text(strip_markdown($scopeAndContent), 150));
        }

        $this->digitalObjectLink = $this->resource->getDigitalObjectUrl();
    }

    protected function addField($validatorSchema, $name)
    {
        switch ($name) {
            case 'levelOfDescription':
                $forbiddenValues = [];
                foreach ($this->resource->ancestors->orderBy('rgt') as $item) {
                    if (isset($item->levelOfDescription)) {
                        switch ($item->levelOfDescription->getName(['sourceCulture' => true])) {
                            case 'Item':
                                $forbiddenValues[] = 'Item';

                                // no break
                            case 'File':
                                $forbiddenValues[] = 'File';

                                // no break
                            case 'Sub-subseries':
                                $forbiddenValues[] = 'Sub-subseries';

                                // no break
                            case 'Subseries':
                                $forbiddenValues[] = 'Subseries';

                                // no break
                            case 'Series':
                                $forbiddenValues[] = 'Series';

                                // no break
                            case 'Sub-subfonds':
                                $forbiddenValues[] = 'Sub-subfonds';

                                // no break
                            case 'Subfonds':
                                $forbiddenValues[] = 'Subfonds';

                                // no break
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

                $validatorSchema->levelOfDescription = new QubitValidatorForbiddenValues([
                    'forbidden_values' => $forbiddenValues,
                    'required' => true,
                ]);

                break;
        }
    }
}
