<?php decorate_with('layout_2col') ?>

<?php slot('title') ?>
  <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>
<?php end_slot() ?>

<?php slot('sidebar') ?>

  <section>
    <h3><?php echo __('Browse by') ?></h3>
    <ul>
      <li><?php echo link_to(__('Archival descriptions'), array('module' => 'informationobject', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(__('Institutions'), array('module' => 'repository', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(__('Subjects'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 35)) ?></li>
      <li><?php echo link_to(__('People &amp; Organizations'), array('module' => 'actor', 'action' => 'browse')) ?></li>
      <li><?php echo link_to(__('Places'), array('module' => 'taxonomy', 'action' => 'browse', 'id' => 42)) ?></li>
      <li><?php echo link_to(__('Newest additions'), array('module' => 'search', 'action' => 'updates')) ?></li>
    </ul>
  </section>

  <?php echo get_component('default', 'popular') ?>

<?php end_slot() ?>

<div class="page">
  <?php echo render_value($resource->getContent(array('cultureFallback' => true))) ?>
</div>

<?php if (SecurityCheck::hasPermission($sf_user, array('module' => 'staticpage', 'action' => 'update'))): ?>
  <?php slot('after-content') ?>
    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'staticpage', 'action' => 'edit'), array('title' => __('Edit this page'), 'class' => 'c-btn')) ?></li>
      </ul>
    </section>
  <?php end_slot() ?>
<?php endif; ?>
