<?php if (count($rights = $resource->getRights()) > 0): ?>
  <?php foreach($rights as $right): ?>
    <?php echo get_partial('right/right', array('resource' => $right->getObject())) ?>
  <?php endforeach; ?>
<?php endif; ?>
<div class="field">
  <?php echo link_to(__('Create new rights'), array($resource, 'sf_route' => 'slug/default', 'module' => 'right', 'action' => 'edit')) ?>
 </div>
