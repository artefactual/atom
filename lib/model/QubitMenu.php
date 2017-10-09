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

class QubitMenu extends BaseMenu
{
  public $disableNestedSetUpdating = false;

  const

    // Root menu
    ROOT_ID = 1,

    // 2nd generation constant ids
    MAIN_MENU_ID = 2,
    QUICK_LINKS_ID  = 3,
    BROWSE_ID = 4,

    // 3rd generation constant ids
    ADD_EDIT_ID = 5,
    IMPORT_ID = 7,
    TRANSLATE_ID = 8,
    ADMIN_ID = 9,
    MANAGE_ID = 10,

    // 4rd generation constant ids
    TAXONOMY_ID = 6;

  /**
   * Wrapper for BaseMenu::getPath() call to allow additional functionality
   *  option 'resolveAlias' - resolve aliases into full path
   *  option 'getUrl' - resolve path to internal or external URL
   *
   * @param array $options Optional parameters
   * @return string Path or url for menu
   */
  public function getPath($options = array())
  {
    // 'currentRealm' is used by menu items on the Institutional Block as part of the
    // enable_institutional_scoping feature. Try using search-realm if available, else
    // try resource->id from sf_route, else try repos param on request.
    if (null !== sfContext::getInstance()->user->getAttribute('search-realm'))
    {
      $currentRealm = sfContext::getInstance()->user->getAttribute('search-realm');
    }
    else if (isset(sfContext::getInstance()->request->getAttribute('sf_route')->resource->id))
    {
      $currentRealm = sfContext::getInstance()->request->getAttribute('sf_route')->resource->id;
    }
    else if (null !== sfContext::getInstance()->request->getParameter('repos'))
    {
      $currentRealm = sfContext::getInstance()->request->getParameter('repos');
    }
    else
    {
      $currentRealm = null;
    }

    $currentSlug = isset(sfContext::getInstance()->request->getAttribute('sf_route')->resource->slug) ?
      sfContext::getInstance()->request->getAttribute('sf_route')->resource->slug : null;

    $aliases = array(
      '%profile%' => sfContext::getInstance()->routing->generate(null, array('module' => 'user', 'slug' => sfContext::getInstance()->user->getUserSlug())),
      '%currentId%' => sfContext::getInstance()->request->id,
      '%currentSlug%' => $currentSlug,
      '%currentRealm%' => $currentRealm
    );

    $path = parent::offsetGet('path', $options);

    if (isset($options['resolveAlias']) && $options['resolveAlias'])
    {
      foreach ($aliases as $alias => $target)
      {
        if (false !== strpos($path, $alias))
        {
          $path = str_replace($alias, $target, $path);
        }
      }
    }

    if (isset($options['getUrl']) && true == $options['getUrl'])
    {
      // Catch any exceptions thrown from url_for() to prevent ugly errors when
      // admin puts in a bad route
      try
      {
        $url = url_for($path);
      }
      catch (Exception $e)
      {
        // if exception caught then return a blank route (home page)
        $url = url_for('');
      }

      $path = $url;
    }

    return $path;
  }

  /**
   * Test if this menu is protected (can't delete)
   *
   * @return boolean
   */
  public function isProtected()
  {
    return in_array($this->id, array(
      QubitMenu::ROOT_ID,
      QubitMenu::MAIN_MENU_ID,
      QubitMenu::QUICK_LINKS_ID,
      QubitMenu::ADD_EDIT_ID,
      QubitMenu::ADMIN_ID));
  }

  /**
   * Return name of menu if object is cast as string
   *
   * @return string menu name
   */
  public function __toString()
  {
    return (string) $this->name;
  }

  /**
   * Find menu by name
   *
   * @param string $topMenuName name of top ancestor
   * @param array $options optional parameters
   * @return array of menu columns, with an additional 'depth' column
   */
  public static function getByName($menuName, $options = array())
  {
    $criteria = new Criteria;
    $criteria->add(QubitMenu::NAME, $menuName);

    return QubitMenu::getOne($criteria);
  }

  /**
   * Test if this menu is selected (based on current module/action)
   *
   * @return boolean
   */
  public function isSelected()
  {
    $currentModule = sfContext::getInstance()->getModuleName();
    $currentAction = sfContext::getInstance()->getActionName();
    $currentUrl = url_for($currentModule.'/'.$currentAction);
    $isSelected = false;

    // Yucky Hack: Don't display "static" menu as selected when displaying
    // an action from staticpage module (See FIXME below)
    if ($currentModule == 'staticpage' && in_array($currentAction, array('edit', 'index', 'list', 'static')))
    {
      return false;
    }
    // Yucky Hack, Part Deux: Don't display any active menu options when
    // displaying search results
    if ($currentModule == 'search' && $currentAction == 'search')
    {
      return false;
    }
    // 'Hacks 3: Return of the Hack' Select the 'archival description' button
    // when uploading digital object
    if ($currentModule == 'digitalobject' && $currentAction == 'edit')
    {
      return ($this->getPath() == 'informationobject/list');
    }
    // And even more hacks
    else if (in_array($currentModule, array('sfIsadPlugin', 'sfRadPlugin', 'sfDcPlugin', 'sfModsPlugin', 'arDacsPlugin')))
    {
      return ($this->getPath() == 'informationobject/list');
    }

    // son of hack
    if (in_array($currentModule, array('term', 'taxonomy')))
    {
      return ($this->getPath() == 'taxonomy/list');
    }

    // If passed $url matches the url for this menu AND is not the base url
    // for the application (url_for()), return true
    $menuUrl = $this->getPath(array('getUrl' => true, 'resolveAlias' => true));
    if ($menuUrl == $currentUrl && $currentUrl != url_for(''))
    {
      $isSelected = true;
    }

    /***
     * FIXME Implement a better way to determine if a menu is selected than
     * the "current module = menu module" paradigm
     ***/

    // if 'module/action' is returned from getPath, then test if module matches
    // current module
    if (preg_match('|^([a-zA-Z]+)/(.+)|', $this->getPath(), $matches))
    {
      if ($matches[1] == $currentModule && $matches[2] == $currentAction)
      {
        $isSelected = true;
      }
    }

    return $isSelected;
  }

  /**
   * Test if a descendant of this menu is selected.
   *
   * @return boolean
   */
  public function isDescendantSelected()
  {
    foreach ($this->getDescendants() as $menu)
    {
      if ($menu->isSelected())
      {
        return true;
      }
    }

    return false;
  }

  /**
   * Get children (only 1st generation) of current menu.
   *
   * @return QubitQuery found children
   */
  public function getChildren()
  {
    $c = new Criteria;
    $c->add(QubitMenu::PARENT_ID, $this->id);
    $c->addAscendingOrderByColumn(QubitMenu::LFT);

    return QubitMenu::get($c);
  }

  /**
   * Move this menu before $referenceMenu
   *
   * @param integer reference menu id
   * @return QubitMenu $this object
   */
  public function moveBeforeById($referenceMenuId)
  {
    if (null !== $prev = QubitMenu::getById($referenceMenuId))
    {
      $this->moveToPrevSiblingOf($prev);
    }

    return $this;
  }

  /**
   * Move this menu after $referenceMenu
   *
   * @param integer reference menu id
   * @return QubitMenu $this object
   */
  public function moveAfterById($referenceMenuId)
  {
    if (null !== $next = QubitMenu::getById($referenceMenuId))
    {
      $this->moveToNextSiblingOf($next);
    }

    return $this;
  }

  /**
   * Find top menu by id, then get all descendents with relative depth
   *
   * @param string $topMenuName name of top ancestor
   * @param array $options optional parameters
   * @return array of menu columns, with an additional 'depth' column
   */
  public static function getTreeById($id, $options=array())
  {
    // Attempt to grab topMenu object via id
    if (null === $topMenu = QubitMenu::getById($id))
    {
      return false;
    }

    return QubitMenu::getTree($topMenu, $options);
  }

  /**
   * Retrieve the current menu hierarchy as a two-dimensional array. Each row in
   * the array includes a 'depth' column (relative to the root of the tree) to
   * aid in formatting the tree for display.
   *
   * @param QubitMenu $topMenu top ancestor
   * @param array $options optional parameters
   * @return array of menu columns, with an additional 'depth' column
   */
  public static function getTree(QubitMenu $topMenu, $options=array())
  {
    $maxDepth = 0;

    if (isset($options['maxDepth']) && is_int($options['maxDepth']))
    {
      $maxDepth = ($options['maxDepth'] > 0) ? $options['maxDepth'] : 0;
    }

    // Get all descendents of "top" menu
    $criteria = new Criteria;
    $criteria->add(QubitMenu::LFT, $topMenu->getLft(), Criteria::GREATER_THAN);
    $criteria->addAnd(QubitMenu::RGT, $topMenu->getRgt(), Criteria::LESS_THAN);
    $criteria->addAscendingOrderByColumn(QubitMenu::LFT);
    $menus = QubitMenu::get($criteria);

    // labouriously calculate depth of current menu from top of hierarchy by
    // looping through results and tracking "ancestors"
    $ancestors = array($topMenu->id);
    foreach ($menus as $menu)
    {
      $thisParentId = $menu->getParentId();
      if ($ancestors[count($ancestors) - 1] != $thisParentId)
      {
        if (!in_array($thisParentId, $ancestors))
        {
          array_push($ancestors, $thisParentId);
        }
        else
        {
          while ($ancestors[count($ancestors) - 1] != $thisParentId)
          {
            array_pop($ancestors);
          }
        }
      }

      // Limit depth of descendants to $maxDepth
      $depth = count($ancestors);
      if ($maxDepth == 0 || $depth <= $maxDepth)
      {
        $menuTree[] = array(
          'id' => $menu->id,
          'parentId' => $menu->getParentId(),
          'name' => $menu->getName(array('cultureFallback' => true)),
          'label' => $menu->getLabel(array('cultureFallback' => true)),
          'depth' => $depth,
          'protected' => ($menu->isProtected()) ? true : false
        );
      }
    }

    return $menuTree;
  }

  /**
   * Display a menu hierarchy as a nested XHTML list.
   *
   * NOTE: This function is a hack that violates the rules of MVC code/template
   * separation; unfortunately, this was by far the cleanest method I could devise
   * for accurately representing a menu heirarchy as a nested XHTML list.
   *
   * @param QubitMenu $parent parent menu object for hierarchy branch
   * @param integer $depth current (relative) depth from top of tree (for styling)
   * @param array $options optional parameters
   * @return string an indented, nested XHTML list
   */
  public static function displayHierarchyAsList($parent, $depth = 0, $options = array())
  {
    // Set current depth if not defined yet
    // We're using it to track the depth of the recursion
    if (!isset($options['current-depth']))
    {
      $options['current-depth'] = 0;
    }

    // An array of <li/> elements for the list
    $li = array();

    foreach ($parent->getChildren() as $child)
    {
      // Skip this menu and children if marked "hidden"
      if (isset($options['overrideVisibility'][$child->getName()])
        && !$options['overrideVisibility'][$child->getName()])
      {
        continue;
      }

      // Determine if we need to keep going down the hierarchy
      $continueHierarchy = $options['current-depth'] < $depth && $child->hasChildren();

      // Declare some options for the link for this node
      $anchorPath = $child->getPath(array('getUrl' => true, 'resolveAlias' => true));

      // Parse module and action from path
      $routeProperties = sfContext::getInstance()->getRouting()->findRoute($anchorPath);
      $module = $routeProperties['parameters']['module'];
      $action = $routeProperties['parameters']['action'];

      // Skip menu item if user doesn't have access to action due to security.yml rules
      if (!sfContext::getInstance()->getUser()->checkModuleActionAccess($module, $action))
      {
        continue;
      }

      $anchorLabel = $child->getLabel(array('cultureFallback' => true));
      $anchorOptions = array();
      if ($continueHierarchy)
      {
        $anchorLabel .= ' <b class="caret"></b>';
        $anchorOptions['class'] = 'dropdown-toggle';
        $anchorOptions['data-toggle'] = 'dropdown';
      }

      // Construct the link
      $a = link_to($anchorLabel, $anchorPath, $anchorOptions);

      // An array of CSS classes for the li element
      $class = array();
      if ($child->isSelected() || $child->isDescendantSelected())
      {
        $class[] = 'active';
      }

      if ($continueHierarchy)
      {
        // Nested nodes
        $a .= self::displayHierarchyAsList($child, $depth, array_merge($options, array('ulWrap' => true, 'ulClass' => 'dropdown-menu', 'current-depth' => ($depth + 1))));

        // We need this class for the <li> element
        $class[] = 'dropdown';
      }
      else
      {
        // Add .leaf to the <li> element of orphan nodes
        $class[] = 'leaf';
      }

      // Build string of classes for the class property of the <li> element
      $class = implode(' ', $class);
      if (0 < strlen($class))
      {
        $class = ' class="'.$class.'"';
      }

      // Add an #id to make style customizatino easier
      $id = isset($child->name) ? ' id="node_'.$child->name.'"' : '';

      $li[] = '<li'.$class.$id.'>'.$a.'</li>';
    }

    if (isset($options['ulWrap']))
    {
      if (!empty($options['ulClass']))
      {
        return '<ul class="'.$options['ulClass'].'">'.implode($li).'</ul>';
      }
      else
      {
        return '<ul class="clearfix links">'.implode($li).'</ul>';
      }
    }

    return implode($li);
  }
}
