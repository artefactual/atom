<div class="text-center">
  <div id="content" class="d-inline-block mt-5 text-start" role="alert">
    <h1 class="h2 mb-0 p-3 border-bottom d-flex align-items-center">
      <i class="fas fa-fw fa-lg fa-exclamation-triangle me-3" aria-hidden="true"></i>
      <?php echo __(
          'Sorry, you do not have permission to make %1% language translations',
          ['%1%' => format_language($sf_user->getCulture())]
      ); ?>
    </h1>

    <div class="p-3">
      <p class="mb-0">
        <a href="javascript:history.go(-1)">
          <?php echo __('Back to previous page.'); ?>
        </a><br>
        <?php echo link_to(__('Go to homepage.'), '@homepage'); ?>
      </p>
    </div>
  </div>
</div>
