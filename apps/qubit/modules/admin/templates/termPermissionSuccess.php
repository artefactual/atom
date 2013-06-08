<section class="admin-message" id="error-term-permission">

  <?php if (null === $use): ?>
    <h2><?php echo __('Sorry, this Term is locked and cannot be deleted') ?></h2>
    <p><?php echo __('The existing term values are required by the application to operate correctly') ?></p>
  <?php else: ?>
    <h2><?php echo __('Sorry, this Term is locked') ?></h2>
    <p><?php echo __('This is a non-preferred term and cannot be edited - please use <a href="%1%">%2%</a>.', array('%1%' => url_for(array($use, 'module' => 'term')), '%2%' => $use->__toString())) ?></p>
  <?php endif; ?>

  <div class="tips">
    <p><a href="javascript:history.go(-1)"><?php echo __('Back to previous page') ?></a></p>
    <p><?php echo link_to(__('Go to homepage'), '@homepage') ?></p>
  </div>

</section>
