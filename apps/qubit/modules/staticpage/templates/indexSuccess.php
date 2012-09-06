<div class="page">

  <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>

  <div>
    <?php echo render_value($resource->getContent(array('cultureFallback' => true))) ?>
  </div>

  <?php if (SecurityCheck::hasPermission($sf_user, array('module' => 'staticpage', 'action' => 'update'))): ?>
    <div class="actions section">

      <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

      <div class="content">
        <ul class="links">
          <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'staticpage', 'action' => 'edit'), array('title' => __('Edit this page'))) ?></li>
        </ul>
      </div>

    </div>
  <?php endif; ?>

</div>
