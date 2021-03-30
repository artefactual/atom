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
 * Action Handler for Browse Hierarchy JSON Data.
 *
 * @author Mike Cantelon <mike@artefactual.com>
 */
class BrowseHierarchyDataAction extends DefaultFullTreeViewAction
{
    public function execute($request)
    {
        parent::execute($request);

        $this->resource = QubitInformationObject::getRoot();

        // Impose limit to what nodeLimit parameter can be set to
        $maxItemsPerPage = sfConfig::get('app_treeview_items_per_page_max', 10000);
        if (!ctype_digit($request->nodeLimit) || $request->nodeLimit > $maxItemsPerPage) {
            $request->nodeLimit = $maxItemsPerPage;
        }

        // Do ordering during query as we need to page through the results
        $options = [
            'orderColumn' => 'title',
            'memorySort' => true,
            'skip' => $request->skip,
            'limit' => $request->nodeLimit,
        ];

        // Load the children of the root node (top-level descriptions)
        $data = $this->getChildren($this->resource->id, $options);

        return $this->renderText(json_encode($data));
    }
}
