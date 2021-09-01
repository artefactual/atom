<section class="w-75 mx-auto rounded border border-dark mt-3 border-1 bg-white" role="alert">

  <?php if (null === $use) { ?>
    <h1 class="border-bottom px-3 py-2"><?php echo __('Sorry, this Term is locked and cannot be deleted'); ?></h1>
    <p class="px-3"><?php echo __('The existing term values are required by the application to operate correctly'); ?></p>
  <?php } else { ?>
    <h1 class="border-bottom px-3 py-2"><?php echo __('Sorry, this Term is locked'); ?></h1>
    <p class="px-3"><?php echo __('This is a non-preferred term and cannot be edited - please use <a href="%1%">%2%</a>.', ['%1%' => url_for([$use, 'module' => 'term']), '%2%' => $use->__toString()]); ?></p>
  <?php } ?>

  <div class="px-3">
    <p><a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a></p>
    <p><?php echo link_to(__('Go to homepage'), '@homepage'); ?></p>
  </div>

</section>
