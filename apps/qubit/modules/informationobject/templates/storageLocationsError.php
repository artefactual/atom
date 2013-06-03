<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>
  <h1><?php echo __('Physical storage locations') ?></h1>
  <h2><?php echo __('No results') ?></h2>
<?php end_slot() ?>

<fieldset class="single">

  <div class="fieldset-wrapper">

    <p><?php echo __('Oops, we couldn\'t find any physical storage locations for the current resource.') ?></p>

  </div>

</fieldset>

<?php slot('after-content') ?>
  <section class="actions">
    <ul class="clearfix links">
      <li><?php echo link_to(__('Back'), array($resource, 'module' => 'informationobject', 'action' => 'reports'), array('class' => 'c-btn')) ?></li>
    </ul>
  </section>
<?php end_slot() ?>
