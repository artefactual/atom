<section class="admin-message" id="error-404">

  <h2>
    <i class="fa fa-times"></i>
    <?php echo __('Sorry, page not found') ?>
  </h2>

  <p>
    <?php echo __('Did you type the URL correctly?') ?><br />
    <?php echo __('Did you follow a broken link?') ?>
  </p>

  <div class="tips">
    <p>
      <a href="javascript:history.go(-1)"><?php echo __('Back to previous page') ?></a><br />
      <?php echo link_to(__('Go to homepage'), '@homepage') ?>
    </p>
  </div>

</section>
