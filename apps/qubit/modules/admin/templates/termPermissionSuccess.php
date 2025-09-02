<div class="text-center">
  <div id="content" class="d-inline-block mt-5 text-start" role="alert">
    <h1 class="h2 mb-0 p-3 border-bottom d-flex align-items-center">
      <i class="fas fa-fw fa-lg fa-exclamation-triangle me-3" aria-hidden="true"></i>
      <?php if (null === $use) { ?>
        <?php echo __('Sorry, this Term is locked and cannot be deleted'); ?>
      <?php } else { ?>
        <?php echo __('Sorry, this Term is locked'); ?>
      <?php } ?>
    </h1>

    <div class="p-3">
      <p>
        <?php if (null === $use) { ?>
          <?php echo __('The existing term values are required by the application to operate correctly'); ?>
        <?php } else { ?>
          <?php echo __(
              'This is a non-preferred term and cannot be edited - please use <a href="%1%">%2%</a>.',
              ['%1%' => url_for([$use, 'module' => 'term']), '%2%' => $use->__toString()]
          ); ?>
        <?php } ?>
      </p>

      <p class="mb-0">
        <a href="javascript:history.go(-1)">
          <?php echo __('Back to previous page.'); ?>
        </a><br>
        <?php echo link_to(__('Go to homepage.'), '@homepage'); ?>
      </p>
    </div>
  </div>
</div>
