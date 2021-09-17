<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Physical storage locations'); ?></h1>
  <h2><?php echo __('No results'); ?></h2>
<?php end_slot(); ?>

<fieldset class="single">

  <div class="fieldset-wrapper">

    <p><?php echo __('Oops, we couldn\'t find any physical storage locations for the current resource.'); ?></p>

  </div>

</fieldset>

<?php slot('after-content'); ?>
  <section class="actions mb-3">
    <?php echo link_to(__('Back'), [$resource, 'module' => 'informationobject', 'action' => 'reports'], ['class' => 'btn atom-btn-outline-light']); ?></li>
  </section>
<?php end_slot(); ?>
