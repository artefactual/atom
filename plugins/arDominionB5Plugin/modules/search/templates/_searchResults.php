<?php if ($pager->getNbResults()) { ?>

  <?php foreach ($pager->getResults() as $hit) { ?>
    <?php echo get_partial('search/searchResult', ['hit' => $hit, 'culture' => $culture]); ?>
  <?php } ?>

<?php } else { ?>

  <div class="p-3">
    <?php echo __('We couldn\'t find any results matching your search.'); ?>
  </div>

<?php } ?>
