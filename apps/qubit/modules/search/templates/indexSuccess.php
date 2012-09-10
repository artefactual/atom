<?php if (isset($pager) && $pager->hasResults()): ?>

  <?php echo get_partial('search/searchResults', array('pager' => $pager, 'filters' => $filters)) ?>

<?php endif; ?>
