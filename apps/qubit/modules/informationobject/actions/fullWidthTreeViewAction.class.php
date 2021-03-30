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
 * Action Handler for FullWidth TreeView.
 *
 * @author Andy Koch <koch.andy@gmail.com>
 */
class InformationObjectFullWidthTreeViewAction extends DefaultFullTreeViewAction
{
    public function execute($request)
    {
        parent::execute($request);

        $this->resource = $this->getRoute()->resource;

        // Check that this isn't the root
        if (!isset($this->resource->parent)) {
            $this->forward404();
        }

        // Check user authorization
        if (!QubitAcl::check($this->resource, 'read')) {
            QubitAcl::forwardUnauthorized();
        }

        // Impose limit to what nodeLimit parameter can be set to
        $maxItemsPerPage = sfConfig::get('app_treeview_items_per_page_max', 10000);
        if (
            !intval($request->nodeLimit)
            || $request->nodeLimit < 1
            || $request->nodeLimit > $maxItemsPerPage
        ) {
            $request->nodeLimit = $maxItemsPerPage;
        }

        // Allow the ability to page through children
        $options = [
            'skip' => $request->skip,
            'limit' => $request->nodeLimit,
        ];

        // On first load, retrieve the ancestors of the selected resource, the
        // resource and its siblings, otherwise get only the resource's siblings
        if (
            filter_var(
                $request->getParameter('firstLoad', false),
                FILTER_VALIDATE_BOOLEAN
            )
        ) {
            $data = $this->getAncestorsAndSiblings($options);
        } else {
            $data = $this->getChildren($this->resource->id, $options);
        }

        return $this->renderText(json_encode($data));
    }
}
