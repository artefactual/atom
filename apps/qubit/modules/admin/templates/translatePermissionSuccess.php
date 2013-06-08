<section class="admin-message" id="error-translate-permission">

  <h2><?php echo __('Sorry, you do not have permission to make %1% language translations', array('%1%' => format_language($sf_user->getCulture()))) ?></h2>

  <div class="tips">
    <p><a href="javascript:history.go(-1)"><?php echo __('Back to previous page') ?></a></p>
    <p><?php echo link_to(__('Go to homepage'), '@homepage') ?></p>
  </div>

</section>
