<div style="text-align: center;">
  <?php echo image_tag('lock48') ?>

    <h2 style="font-size: 20px;"><?php echo __('Sorry, you do not have permission to make %1% language translations', array('%1%' => format_language($sf_user->getCulture()))); ?></h2>

  <a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a>
  <br/>
  <?php echo link_to(__('Go to homepage'), '@homepage') ?>

</div>
