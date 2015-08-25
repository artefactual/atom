<div style="text-align: center;">

  <?php echo image_tag('lock48', array('alt' => __('Read only'))) ?>

  <h2 style="font-size: 20px;"><?php echo __('The system is currently in read-only mode. Please try again later.'); ?></h2>

  <a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a>

  <br/>

  <?php echo link_to(__('Go to homepage'), '@homepage') ?>

</div>
