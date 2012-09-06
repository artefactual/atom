<div>
  <h3>
    <?php echo __('Taxonomy hierarchy') ?> &raquo; <?php echo render_title($resource->taxonomy) ?>
  </h3>
  <div>
    <?php echo get_component('term', 'treeView') ?>
  </div>
</div>

<?php echo get_partial('term/format', array('resource' => $resource)) ?>
