<div id="treeview-btn-area">
  <a href="<?php echo url_for(array(
      'module' => 'informationobject', 
      'action' => 'browse', 
      'collection' => $resource->getCollectionRoot()->id 
    )); ?>">
    <i class="icon-list" title="Browse as list"></i> 
  </a>
</div>

<ul id="treeview-menu" class="nav nav-tabs">
  <li>
    <a href="#treeview-search" data-toggle="#treeview-search">
      <?php echo __('Quick search') ?>
    </a>
  </li>
</ul>

<div id="treeview" data-current-id="<?php echo $resource->id ?>" data-sortable="<?php echo $sortable ? 'true' : 'false' ?>">

  <ul class="unstyled">


  </ul>

</div>

<div id="treeview-search">

  <form method="get" action="<?php echo url_for(array('module' => 'search', 'action' => 'index', 'collection' => $resource->getCollectionRoot()->id)) ?>" data-not-found="<?php echo __('No results found.') ?>">
    <div class="search-box">
      <input type="text" name="query" placeholder="<?php echo __('Search titles and identifiers') ?>" />
      <button type="submit"><i class="icon-search"></i></button>
    </div>
  </form>

</div>
