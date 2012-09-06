<div class="section" style="text-align: center;">

  <?php echo image_tag('cancel48') ?>

  <h2 class="capitalize" style="font-size: 20px"><?php echo __('Sorry, page not found') ?></h2>

  <h4><?php echo __('Did you type the URL correctly?') ?></h4>

  <h4><?php echo __('Did you follow a broken link?') ?></h4>

  <a href="javascript:history.go(-1)"><?php echo __('Back to previous page') ?></a>

  <br/><?php echo link_to(__('Go to homepage'), '@homepage') ?>

</div>
