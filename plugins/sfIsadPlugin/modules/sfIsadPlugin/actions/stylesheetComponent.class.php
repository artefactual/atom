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

class sfIsadPluginStylesheetComponent extends sfComponent
{
    public function execute($request)
    {
        // If institutional scoping is on, repo comes from the search-realm
        if (sfConfig::get('app_enable_institutional_scoping') && sfContext::getInstance()->user->hasAttribute('search-realm')) {
            $repository = QubitRepository::getById(sfContext::getInstance()->user->getAttribute('search-realm'));
        }
        // If the feature is off, fall back to the repository component rules.
        elseif (!sfConfig::get('app_enable_institutional_scoping')) {
            if (!isset($request->getAttribute('sf_route')->resource)) {
                return sfView::NONE;
            }

            $resource = $request->getAttribute('sf_route')->resource;

            switch (true) {
                case $resource instanceof QubitInformationObject:
                    $repository = $resource->getRepository(['inherit' => true]);

                    break;

                case $resource instanceof QubitRepository:
                    $repository = $resource;

                    break;

                default:
                    return sfView::NONE;
            }
        } else {
            return sfView::NONE;
        }

        if (null === $repository || null === $repository->backgroundColor) {
            return sfView::NONE;
        }

        // Get value
        $this->backgroundColor = $repository->backgroundColor->__toString();

        if (0 == strlen($this->backgroundColor)) {
            return sfView::NONE;
        }
    }
}
