<?php echo get_partial('layout_start'); ?>

<div id="wrapper" class="container-xxl py-3 flex-grow-1" role="main">
  <?php echo get_partial('alerts'); ?>
  <?php echo $sf_content; ?>
</div>

<?php echo get_partial('layout_end'); ?>
