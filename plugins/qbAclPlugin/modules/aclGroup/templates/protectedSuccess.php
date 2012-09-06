<div style="text-align: center;">

  <?php echo image_tag('lock48') ?>

  <h2 style="font-size: 20px;"><?php echo __('Sorry, this group is locked and cannot be deleted'); ?></h2>

  <h2><?php echo __('This group is required by the application to operate correctly'); ?></h2>

  <a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a>

  <br/>

  <?php echo link_to(__('List groups'), array('module' => 'aclGroup', 'action' => 'list')) ?>

</div>