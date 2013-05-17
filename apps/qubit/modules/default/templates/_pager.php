<?php if ($pager->haveToPaginate()): ?>

  <section>

    <div class="result-count">
      <?php if (0 < $pager->getNbResults()): ?>
        <?php echo __('Results %1% to %2% of %3%', array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>
      <?php else: ?>
        <?php echo __('No results') ?>
      <?php endif; ?>
    </div>

    <div class="visible-phone">
      <div class="pager">
        <ul>

          <?php if (1 < $pager->getPage()): ?>
            <li class="previous">
              <?php echo link_to('&laquo; '. __('Previous'), array('page' => $pager->getPage() - 1) + $sf_request->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

          <?php if ($pager->getLastPage() > $pager->getPage()): ?>
            <li class="next">
              <?php echo link_to(__('Next'). ' &raquo;', array('page' => $pager->getPage() + 1) + $sf_request->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>

  </section>

<?php endif; ?>
