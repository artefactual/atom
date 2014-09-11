<?php if ($pager->hasResults()): ?>

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <?php echo get_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager, 'culture' => $culture)) ?>
  <?php endforeach; ?>

<?php else: ?>

  <div>
    <h2><?php echo __('We couldn\'t find any results matching your search.') ?></h2>
  </div>

<?php endif; ?>
