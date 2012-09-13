<?php use_helper('Text') ?>

<?php if ('print' == $sf_request->getParameter('media')): ?>
<div id="preview-message">
  <?php echo __('Print preview') ?>
  <?php echo link_to('Close', array_diff($sf_request->getParameterHolder()->getAll(), array('media' => 'print'))) ?>
</div>
<?php endif; ?>

<h1 class="label">
  <?php echo ('Advanced search') ?>

  <div id="action-icons">
    <?php echo link_to(
      image_tag('printer-icon.png', array('alt' => __('Print'))),
        array_merge($sf_request->getParameterHolder()->getAll(), array('media' => 'print')),
        array('title' => __('Print'))) ?>
  </div>
</h1>

<?php if ('print' != $sf_request->getParameter('media')): ?>
  <?php echo get_partial('search/advancedSearch', array('form' => $form, 'action' => 'advanced')) ?>
<?php else: ?>
  <?php echo get_partial('printAdvancedSearchTerms', array('queryTerms' => $queryTerms)) ?>
<?php endif; ?>

<?php if (isset($error)): ?>

  <div class="error">
    <ul>
      <li><?php echo $error ?></li>
    </ul>
  </div>

<?php endif; ?>

<?php if (isset($pager)): ?>

  <?php echo get_partial('search/searchResults', array('pager' => $pager, 'timer' => $timer)) ?>

<?php endif; ?>
