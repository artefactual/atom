<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>

  <?php if (isset($preview)) { ?>
    <div class="copyright-statement-preview alert alert-info">
      <?php echo __('Copyright statement preview'); ?>
    </div>
  <?php } ?>

  <h1><?php echo render_title($resource); ?></h1>

<?php end_slot(); ?>

<div class="page">

  <div class="p-3">
    <?php echo render_value_html($sf_data->getRaw('copyrightStatement')); ?>
  </div>

</div>

<?php slot('after-content'); ?>
  <form method="get">
    <input type="hidden" name="token" value="<?php echo $accessToken; ?>">
    <?php if (isset($preview)) { ?>
      <ul class="actions mb-3 nav gap-2">
        <li><button class="btn atom-btn-outline-success" type="submit" disabled="disabled"><?php echo __('Agree'); ?></button></li>
        <li><?php echo link_to(__('Close'), ['module' => 'settings', 'action' => 'permissions'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li> 
      </ul>
    <?php } else { ?>
      <section class="actions mb-3">
        <button class="btn atom-btn-outline-success" type="submit"><?php echo __('Agree'); ?></button>
      </section>
    <?php } ?>
  </form>
<?php end_slot(); ?>
