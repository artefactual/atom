<?php use_helper('Text') ?>

<?php if ('print' == $sf_request->getParameter('media')): ?>
<div id="preview-message">
  <?php echo __('Print preview') ?>
  <?php echo link_to('Close', array('module' => 'search', 'action' => 'index', 'query' => $sf_request->query)) ?>
</div>
<?php endif; ?>

<h1><?php echo __('Search results') ?></h1>

<h1 class="label">
  <?php echo esc_entities($title) ?>

  <div id="action-icons">
    <?php echo link_to(
      image_tag('printer-icon.png', array('alt' => __('Print'))),
        array('module' => 'search', 'action' => 'index', 'query' => $sf_request->query, 'media' => 'print'),
        array('title' => __('Print'))) ?>
  </div>
</h1>

<?php if (isset($error)): ?>

  <div class="error">
    <ul>
      <li><?php echo $error ?></li>
    </ul>
  </div>

<?php else: ?>
  <?php if (isset($pager)): ?>

    <?php echo get_partial('search/searchResults', array('pager' => $pager, 'timer' => $timer)) ?>

  <?php endif; ?>

<?php endif; ?>
