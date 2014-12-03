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
              <?php echo link_to('&laquo; '. __('Previous'), array('page' => $pager->getPage() - 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

          <?php if ($pager->getLastPage() > $pager->getPage()): ?>
            <li class="next">
              <?php echo link_to(__('Next'). ' &raquo;', array('page' => $pager->getPage() + 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>

    <div class="hidden-phone">
      <div class="pagination pagination-centered">
        <ul>

          <?php $items = 7 ?>

          <?php if (1 < $pager->getPage()): ?>
            <li class="previous">
              <?php echo link_to('&laquo; '. __('Previous'), array('page' => $pager->getPage() - 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

          <?php foreach ($pager->getLinks($items) as $key => $page): ?>

            <?php if (0 === $key): ?>

              <?php if ($pager->getPage() == $page): ?>
                <li class="active"><span>1</span></li>
              <?php else: ?>
                <li><?php echo link_to(1, array('page' => 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => __('Go to page %1%', array('%1%' => 1)))) ?></li>
              <?php endif; ?>

              <?php if (1 == $page): ?>
                <?php continue; ?>
              <?php else: ?>
                <li class="dots"><span>...</span></li>
              <?php endif; ?>

            <?php endif; ?>

            <?php if ($pager->getPage() == $page): ?>
              <li class="active"><span><?php echo $page ?></span></li>
            <?php else: ?>
              <li><?php echo link_to($page, array('page' => $page) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => __('Go to page %1%', array('%1%' => $page)))) ?></li>
            <?php endif; ?>

          <?php endforeach ?>

          <?php if (floor($items/2)  < ($pager->getLastPage() - $pager->getPage())): ?>
            <li class="dots"><span>...</span></li>
            <li class="last">
              <?php echo link_to($pager->getLastPage(), array('page' => $pager->getLastPage()) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

          <?php if ($pager->getLastPage() > $pager->getPage()): ?>
            <li class="next">
              <?php echo link_to(__('Next'). ' &raquo;', array('page' => $pager->getPage() + 1) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
            </li>
          <?php endif; ?>

        </ul>
      </div>
    </div>

  </section>

<?php endif; ?>
