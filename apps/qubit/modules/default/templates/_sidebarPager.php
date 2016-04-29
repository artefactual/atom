<?php if ($pager->haveToPaginate()): ?>

  <section>

    <div class="pagination-centered sidebar-pager-results">

      <?php echo __('Results <span class="result-start">%1%</span> to <span class="result-end">%2%</span> of %3%',
        array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>

      <div>
        <button class="prev btn btn-small fa fa-arrow-left"></button>

        <input id="sidebar-pager-input" autocomplete="off" type="text" value="1">
         of <?php echo $pager->getLastPage() ?>
        </input>

        <button class="next btn btn-small fa fa-arrow-right"></button>
      </div>

    </div>

  </section>

<?php endif; ?>
