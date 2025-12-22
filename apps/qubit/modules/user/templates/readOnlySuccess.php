<div style="text-align: center;">

  <?php echo image_tag('lock48', ['alt' => __('Read only')]); ?>

  <h2 style="font-size: 20px;"><?php echo __('The system is currently in read-only mode. Please try again later.'); ?></h2>

  <a href="#" data-action="back"
    data-fallback-url="<?php echo url_for('@homepage'); ?>"><?php echo __('Back to previous page'); ?>
  </a>
  <br />

  <?php echo link_to(__('Go to homepage'), '@homepage'); ?>

</div>
