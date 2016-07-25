<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>

  <?php if (isset($preview)): ?>
    <div class="copyright-statement-preview alert alert-info">
      <?php echo __('Copyright statement preview') ?>
    </div>
  <?php endif; ?>

  <h1><?php echo render_title($resource->getTitle(array('cultureFallback' => true))) ?></h1>

<?php end_slot() ?>

<div class="page">

  <div>
    <?php echo render_value($sf_data->getRaw('copyrightStatement')) ?>
  </div>

</div>

<?php slot('after-content') ?>
  <form method="get">
    <input type="hidden" name="token" value="<?php echo $accessToken ?>"/>
    <section class="actions">
      <ul>
        <?php if (isset($preview)): ?>
          <li><button class="c-btn c-btn-submit" type="submit" disabled="disabled"><?php echo __('Agree') ?></button></li>
          <li><a class="c-btn" href="javascript:window.close();"><?php echo __('Close') ?></a>
        <?php else: ?>
          <li><button class="c-btn c-btn-submit" type="submit"><?php echo __('Agree') ?></button></li>
        <?php endif; ?>
      </ul>
    </section>
  </form>
<?php end_slot() ?>
