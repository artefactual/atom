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
 * List/edit current menu tree
 *
 * @package    AccesstoMemory
 * @subpackage menu
 * @author     David Juhasz <david@artefactual.com>
 */
class MenuListAction extends sfAction
{
  public function execute($request)
  {
    // Re-order menus if "move" parameter passed
    if (isset($request->move) && $menu = QubitMenu::getById($request->move))
    {
      if (isset($request->before))
      {
        $menu->moveBeforeById($request->before);
      }
      else if (isset($request->after))
      {
        $menu->moveAfterById($request->after);
      }

      // Remove cache
      if ($this->context->getViewCacheManager() !== null)
      {
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_browseMenu&sf_cache_key=*');
        $this->context->getViewCacheManager()->remove('@sf_cache_partial?module=menu&action=_mainMenu&sf_cache_key=*');
      }
    }

    // Get an array with menu ids and depth (relative to top menu) to create
    // and indented list
    $this->menuTree = QubitMenu::getTreeById(QubitMenu::ROOT_ID);

    foreach ($this->menuTree as $i => $menu)
    {
      // Build an array of siblings for each parentId for figuring out
      // prev/next buttons
      $siblingList[$menu['parentId']][] = array('id' => $menu['id'], 'pos' => $i);
    }

    // Build prev/next values based on number of siblings
    foreach ($siblingList as $siblings)
    {
      foreach ($siblings as $i => $sibling)
      {
        if (0 < $i)
        {
          $this->menuTree[$sibling['pos']]['prev'] = $siblings[$i - 1]['id'];
        }

        if (count($siblings) - 1 > $i)
        {
          $this->menuTree[$sibling['pos']]['next'] = $siblings[$i + 1]['id'];
        }
      }
    }
  }
}
