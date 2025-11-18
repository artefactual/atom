<h4 class="h5 mb-2"><?php echo __('Results'); ?></h4>
<ul class="list-unstyled">
  <li><?php echo $results; ?></li>
</ul>

<?php if (QubitTerm::ROOT_ID != $resource->parent->id) { ?>
  <h4 class="h5 mb-2"><?php echo __('Broader term'); ?></h4>
  <ul class="list-unstyled">
    <li><?php echo link_to(render_title($resource->parent), [$resource->parent, 'module' => 'term']); ?></li>
  </ul>
<?php } ?>

<h4 class="h5 mb-2"><?php echo __('No. narrower terms'); ?></h4>
<ul class="list-unstyled">
  <li><?php echo count($resource->getChildren()); ?></li>
</ul>

<?php if (count(QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID])) > 0) { ?>
  <h4 class="h5 mb-2"><?php echo __('Related terms'); ?></h4>
  <ul class="list-unstyled">
    <?php foreach (QubitRelation::getBySubjectOrObjectId($resource->id, ['typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID]) as $item) { ?>
      <li><?php echo link_to(render_title($item->getOpposedObject($resource->id)), [$item->getOpposedObject($resource->id), 'module' => 'term']); ?></li>
    <?php } ?>
  </ul>
<?php } ?>
