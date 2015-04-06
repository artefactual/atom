<?php if ($pager->haveToPaginate()): ?>

  <section>

    <div class="pagination-centered sidebar-pager-results">

      <?php echo __('Results <span class="result-start">%1%</span> to <span class="result-end">%2%</span> of %3%',
        array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>

      <div class="buttons">
        <button class="prev btn btn-small">&laquo;<?php echo __('Previous') ?></button>
        <button class="next btn btn-small"><?php echo __('Next') ?>&raquo;</button>
      </div>

    </div>

  </section>

<?php endif; ?>
