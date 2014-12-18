<?php if ($pager->hasResults()): ?>

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $culture)) ?>
  <?php endforeach; ?>

<?php else: ?>

  <div>
    <h2><?php echo __('We couldn\'t find any results matching your search.') ?></h2>
  </div>

<?php endif; ?>
