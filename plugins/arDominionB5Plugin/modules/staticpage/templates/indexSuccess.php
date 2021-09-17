<?php $menu = get_component('menu', 'staticPagesMenu'); ?>
<?php $layout = 'layout_1col'; ?>
<?php if (!empty($menu)) { ?>
  <?php $layout = 'layout_2col'; ?>
  <?php slot('sidebar'); ?>
    <?php echo $menu; ?>
  <?php end_slot(); ?>
<?php } ?>
<?php decorate_with($layout); ?>

<?php slot('title'); ?>
  <h1><?php echo render_title($resource->getTitle(['cultureFallback' => true])); ?></h1>
<?php end_slot(); ?>

<div class="page p-3">

  <div>
    <?php echo render_value_html($sf_data->getRaw('content')); ?>
  </div>

</div>

<?php if (QubitAcl::check($resource, 'update')) { ?>
  <?php slot('after-content'); ?>
    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'staticpage', 'action' => 'edit'], ['class' => 'btn atom-btn-outline-light']); ?></li>
      <?php if (QubitAcl::check($resource, 'delete')) { ?>
        <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'staticpage', 'action' => 'delete'], ['class' => 'btn atom-btn-outline-danger']); ?></li>
      <?php } ?>
    </ul>
  <?php end_slot(); ?>
<?php } ?>
