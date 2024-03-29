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
 * Display "quicklinks" navigation menu.
 *
 * @author     David Juhasz <david@artefactual.com>
 */
class menuQuickLinksMenuComponent extends sfComponent
{
    public function execute($request)
    {
        // Get menu
        $quickLinksMenu = QubitMenu::getById(QubitMenu::QUICK_LINKS_ID);

        if (!$quickLinksMenu instanceof QubitMenu) {
            return;
        }

        // Get menu items that correspond to an external URL or an internal path
        $this->quickLinks = [];

        foreach ($quickLinksMenu->getChildren() as $child) {
            $url = $child->getPath(['getUrl' => true, 'resolveAlias' => true, 'removeIndex' => true]);
            $urlParsed = parse_url($url);

            if (isset($urlParsed['scheme']) || QubitObject::actionExistsForUrl($url)) {
                $this->quickLinks[] = $child;
            }
        }
    }
}
