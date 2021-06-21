<?php echo get_partial('layout_start'); ?>

<div id="wrapper" class="container-xl my-3" role="main">
  <?php echo get_partial('alerts'); ?>
  <?php include_slot('title'); ?>
  <?php include_slot('before-content'); ?>
  <?php if (!include_slot('content')) { ?>
    <div id="content">
      <?php echo $sf_content; ?>
    </div>
  <?php } ?>
  <?php include_slot('after-content'); ?>
</div>

<?php echo get_partial('layout_end'); ?>
