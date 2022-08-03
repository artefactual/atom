<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Search error encountered'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="messages error">
    <div>
      <p>
        <strong><?php echo __(
            'An error occurred with your requested search. Please try a different search.'
        ); ?></strong>
        <?php if (!empty($message)) { ?>
          <pre><?php echo $message; ?></pre>
        <?php } ?>
      </p><p>
        <a href="javascript:history.go(-1)"><?php echo __(
            'Back to previous page.'
        ); ?></a>
      </p>
    </div>
  </div>
<?php end_slot(); ?>
