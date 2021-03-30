<?php decorate_with('layout_2col'); ?>

<?php slot('title'); ?>
  <h1><?php echo render_title($resource->getTitle(['cultureFallback' => true])); ?></h1>
<?php end_slot(); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('menu', 'staticPagesMenu'); ?>

  <section>
    <h2><?php echo __('Browse by'); ?></h2>
    <ul>
      <?php $browseMenu = QubitMenu::getById(QubitMenu::BROWSE_ID); ?>
      <?php if ($browseMenu->hasChildren()) { ?>
        <?php foreach ($browseMenu->getChildren() as $item) { ?>
          <li><a href="<?php echo url_for($item->getPath(['getUrl' => true, 'resolveAlias' => true])); ?>"><?php echo esc_specialchars($item->getLabel(['cultureFallback' => true])); ?></a></li>
        <?php } ?>
      <?php } ?>
    </ul>
  </section>

  <?php echo get_component('default', 'popular', ['limit' => 10, 'sf_cache_key' => $sf_user->getCulture()]); ?>

<?php end_slot(); ?>

<div class="page">
  <?php echo render_value_html($sf_data->getRaw('content')); ?>
</div>

<?php if (QubitAcl::check($resource, 'update')) { ?>
  <?php slot('after-content'); ?>
    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Edit'), [$resource, 'module' => 'staticpage', 'action' => 'edit'], ['title' => __('Edit this page'), 'class' => 'c-btn']); ?></li>
      </ul>
    </section>
  <?php end_slot(); ?>
<?php } ?>
