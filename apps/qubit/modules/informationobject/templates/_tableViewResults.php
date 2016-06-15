<?php if ($pager->hasResults()): ?>
  <?php foreach ($pager->getResults() as $hit): ?>
    <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $selectedCulture)) ?>
  <?php endforeach; ?>
<?php else: ?>
  <section id="no-search-results">
    <i class="fa fa-search"></i>
    <p class="no-results-found"><?php echo __('No results found.') ?></p>
  </section>
<?php endif; ?>
