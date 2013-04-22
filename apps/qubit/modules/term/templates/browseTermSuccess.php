<?php decorate_with('layout_2col') ?>
<?php use_helper('Text') ?>

<?php slot('title') ?>

  <div class="hidden-phone">
    <h1>
      <?php if (isset($icon)): ?>
        <?php echo image_tag('/images/icons-large/icon-'.$icon.'.png', array('width' => '42', 'height' => '42')) ?>
      <?php endif; ?>
      <?php echo __('Browse %1% %2%', array(
        '%1%' => render_title($resource->taxonomy),
        '%2%' => render_title($resource))) ?>
    </h1>
  </div>

<?php end_slot() ?>

<?php slot('sidebar') ?>

  <div id="facets">

  </div>

<?php end_slot() ?>

<?php foreach ($pager->getResults() as $hit): ?>
  <?php $doc = $hit->getData() ?>
  <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
<?php endforeach; ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
