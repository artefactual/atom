<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <h1><?php echo __('Search error encountered') ?></h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <div class="messages error">
    <div>
      <strong><?php echo $reason ?></strong>
      <?php if (!empty($error)): ?>
        <pre><?php echo $error ?></pre>
      <?php endif; ?>
    </div>
  </div>

  <p><a href="javascript:history.go(-1)"><?php echo __('Back to previous page.') ?></a></p>

<?php end_slot() ?>
