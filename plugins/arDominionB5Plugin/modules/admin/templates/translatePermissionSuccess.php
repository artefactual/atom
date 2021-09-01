<section class="w-75 mx-auto rounded border border-dark mt-3 border-1 bg-white" role="alert">

  <h1 class="border-bottom px-3 py-2"><?php echo __('Sorry, you do not have permission to make %1% language translations', ['%1%' => format_language($sf_user->getCulture())]); ?></h1>

  <div class="px-3">
    <p><a href="javascript:history.go(-1)"><?php echo __('Back to previous page'); ?></a></p>
    <p><?php echo link_to(__('Go to homepage'), '@homepage'); ?></p>
  </div>

</section>
