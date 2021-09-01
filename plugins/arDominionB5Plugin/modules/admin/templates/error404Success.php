<section class="w-50 mx-auto rounded border border-dark mt-3 border-1 bg-white" role="alert">

  <h1 class="border-bottom px-3 py-2">
    <i class="fa fa-times me-2 text-danger" aria-hidden="true"></i>
    <?php echo __('Sorry, page not found'); ?>
  </h1>

  <p class="px-3">
    <?php echo __('Did you type the URL correctly?'); ?><br />
    <?php echo __('Did you follow a broken link?'); ?>
  </p>

  <div class="px-3">
    <p>
      <a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a><br />
      <?php echo link_to(__('Go to homepage'), '@homepage'); ?>
    </p>
  </div>

</section>
