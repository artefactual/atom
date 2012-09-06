<div style="text-align: center;">
  <?php echo image_tag('lock48') ?>

  <?php if (null == $use): ?>
    <h2 style="font-size: 20px;"><?php echo __('Sorry, this Term is locked and cannot be deleted'); ?></h2>
    <h2><?php echo __('The existing term values are required by the application to operate correctly'); ?></h2>
  <?php else: ?>
    <h2 style="font-size: 20px;"><?php echo __('Sorry, this Term is locked'); ?></h2>
    <h2><?php echo __('This is a non-preferred term and cannot be edited - please use <a href="%1%">%2%</a>.', array('%1%' => url_for(array($use, 'module' => 'term')), '%2%' => $use->__toString())); ?></h2>
  <?php endif; ?>

  <a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a>
  <br/>
  <?php echo link_to(__('Go to homepage'), '@homepage') ?>

</div>
