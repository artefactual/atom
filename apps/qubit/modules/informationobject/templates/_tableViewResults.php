<?php if ($pager->getNbResults()) { ?>
  <?php foreach ($pager->getResults() as $hit) { ?>
    <?php echo get_partial('search/searchResult', ['hit' => $hit, 'culture' => $selectedCulture]); ?>
  <?php } ?>
<?php } else { ?>
  <section id="no-search-results">
    <i class="fa fa-search"></i>
    <p class="no-results-found"><?php echo __('No results found.'); ?></p>
  </section>
<?php } ?>
