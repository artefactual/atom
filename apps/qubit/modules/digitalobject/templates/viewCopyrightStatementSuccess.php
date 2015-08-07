<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>
<?php end_slot() ?>

<div class="page">

  <div>
    <?php echo render_value($sf_data->getRaw('copyrightStatement')) ?>
  </div>

</div>

<?php slot('after-content') ?>
  <form method="post">
    <section class="actions">
      <ul>
        <li><button class="c-btn c-btn-submit" type="submit"><?php echo __('Agree') ?></button></li>
      </ul>
    </section>
  </form>
<?php end_slot() ?>