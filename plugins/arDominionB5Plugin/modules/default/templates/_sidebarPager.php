<?php if ($pager->haveToPaginate()) { ?>

  <nav class="card-body border-bottom p-2 small" aria-label="<?php echo __('Pagination'); ?>">

    <p class="text-center mb-1">
      <?php echo __('Results <span class="result-start">%1%</span> to <span class="result-end">%2%</span> of %3%',
        ['%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults()]); ?>
    </p>

    <ul class="pagination pagination-sm justify-content-center mb-2">

      <li class="page-item">
        <a class="page-link page-link-prev" href="#" aria-label="<?php echo __('Previous'); ?>">
          <i aria-hidden="true" class="fas fa-arrow-left"></i>
        </a>
      </li>

      <li class="page-item my-0 mx-0 text-center">
        <input class="form-control form-control-sm w-50 d-inline-block" autocomplete="off" type="number" value="1" min="<?php echo $pager->getFirstPage(); ?>" max="<?php echo $pager->getLastPage(); ?>">
        <span><?php echo __('of %lastPage%', ['%lastPage%' => $pager->getLastPage()]); ?></span>
      </li>

      <li class="page-item">
        <a class="page-link page-link-next" href="#" aria-label="<?php echo __('Next'); ?>">
          <i aria-hidden="true" class="fas fa-arrow-right"></i>
        </a>
      </li>

    </ul>

  </nav>

<?php } ?>
