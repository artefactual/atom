<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>
<?php end_slot() ?>

<div class="page">

  <div>
    <?php echo $sf_data->getRaw('content') ?>
  </div>

</div>

<?php if (SecurityCheck::hasPermission($sf_user, array('module' => 'staticpage', 'action' => 'update'))): ?>
  <?php slot('after-content') ?>
      <section class="actions">
        <ul>
          <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'staticpage', 'action' => 'edit'), array('class' => 'c-btn c-btn-submit', 'title' => __('Edit this page'))) ?></li>
          <?php if (QubitAcl::check($resource, 'delete')): ?>
            <li><?php echo link_to(__('Delete'), array($resource, 'module' => 'staticpage', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
          <?php endif; ?>
        </ul>
      </section>
  <?php end_slot() ?>
<?php endif; ?>
