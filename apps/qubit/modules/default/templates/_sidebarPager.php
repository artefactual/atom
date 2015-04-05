<?php if ($pager->haveToPaginate()): ?>

  <section>
    <?php if (0 < $pager->getNbResults()): ?>

      <div class="pagination-centered sidebar-pager-results">
        <?php echo __('Results <span id="result-start">%1%</span> to <span id="result-end">%2%</span> of %3%',
              array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>
        <br>
        <button class="previous btn">
          <?php echo '&laquo; '.__('Previous') ?>
        </button>

        <button class="next btn">
          <?php echo __('Next').' &raquo;'?>
        </button>
      </div>

    <?php endif; ?>
  </section>

<?php endif; ?>
