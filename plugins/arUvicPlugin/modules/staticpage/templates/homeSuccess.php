<?php decorate_with('layout_basic') ?>

<?php slot('content') ?>

  <div id="ccap-home-banner-container">
    <div id="ccap-home-banner">
      <h1>Chinese Canadian Artifacts Project</h1>
      <h2>A UVIC-BC Legacy Partnership</h2>
    </div>
  </div>

  <div id="ccap-home-body">

    <div class="container">

      <?php echo render_value($sf_data->getRaw('content')) ?>

      <?php if (SecurityCheck::hasPermission($sf_user, array('module' => 'staticpage', 'action' => 'update'))): ?>
          <section class="actions">
            <ul>
              <li><?php echo link_to(__('Edit'), array($resource, 'module' => 'staticpage', 'action' => 'edit'), array('title' => __('Edit this page'), 'class' => 'c-btn')) ?></li>
            </ul>
      <?php endif; ?>

    </div>

  </div>

  <h2 id="ccap-home-carousel-title"><?php echo __('Most visited this month...') ?></h2>
  <div id="ccap-home-carousel">
    <div>
      <?php foreach ($mostPopularLastMonth as $item): ?>
        <div class="carousel-item">
          <?php echo link_to(image_tag($item['path'].DIRECTORY_SEPARATOR.$item['name']), array('module' => 'informationobject', 'slug' => $item['slug'])) ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

<?php end_slot(); ?>

