<?php echo get_partial('layout_start'); ?>

<div id="wrapper" class="container-xxl pt-3 flex-grow-1">
  <?php echo get_partial('alerts'); ?>
  <div id="main-column" role="main">
    <?php echo $sf_content; ?>
  </div>
</div>

<?php echo get_partial('layout_end'); ?>
