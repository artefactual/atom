<?php

class BrowseHierarchyAction extends sfAction
{
  public function execute($request)
  {
    // Check user authorization
    if (sfConfig::get('app_treeview_show_browse_hierarchy_page', 'no') === 'no')
    {
      QubitAcl::forwardUnauthorized();
    }
  }
}
