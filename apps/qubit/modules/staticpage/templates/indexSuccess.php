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

<div class="page">

  <div>
    <?php echo render_value_html($sf_data->getRaw('content')); ?>
  </div>

</div>

<?php if (QubitAcl::check($resource, 'update')) { ?>
  <?php slot('after-content'); ?>
      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'staticpage', 'action' => 'edit'], ['class' => 'c-btn c-btn-submit', 'title' => __('Edit this page')]); ?></li>
          <?php if (QubitAcl::check($resource, 'delete')) { ?>
            <li><?php echo link_to(__('Delete'), [$resource, 'module' => 'staticpage', 'action' => 'delete'], ['class' => 'c-btn c-btn-delete']); ?></li>
          <?php } ?>
        </ul>
      </section>
  <?php end_slot(); ?>
<?php } ?>
