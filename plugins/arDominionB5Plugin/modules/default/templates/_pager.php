<?php if ($pager->haveToPaginate()) { ?>
<nav aria-label="<?php echo __('Page navigation'); ?>">

  <div class="result-count text-center mb-2">
    <?php if (0 < $pager->getNbResults()) { ?>
      <?php echo __('Results %1% to %2% of %3%', ['%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults()]); ?>
    <?php } else { ?>
      <?php echo __('No results'); ?>
    <?php } ?>
  </div>

  <ul class="pagination justify-content-center">

    <?php $items = 7; ?>

    <?php if ($pager->getPage() > 1) { ?>
      <li class="page-item">
        <?php echo link_to(
          __('Previous'),
          ['page' => $pager->getPage() - 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
          ['class' => 'page-link']
        ); ?>
      </li>
    <?php } else { ?>
      <li class="page-item disabled">
        <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?php echo __('Previous'); ?></a>
      </li>
    <?php } ?>

    <?php foreach ($pager->getLinks($items) as $key => $page) { ?>

      <?php if (0 === $key) { ?>

        <?php if ($pager->getPage() == $page) { ?>
          <li class="page-item active d-none d-sm-block" aria-current="page">
            <span class="page-link">1</span>
          </li>
        <?php } else { ?>
          <li class="page-item d-none d-sm-block">
            <?php echo link_to(
              1,
              ['page' => 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
              ['class' => 'page-link', 'title' => __('Go to page %1%', ['%1%' => 1])]
            ); ?>
          </li>
        <?php } ?>

        <?php if (1 == $page) { ?>
          <?php continue; ?>
        <?php } else { ?>
          <li class="page-item disabled dots d-none d-sm-block">
            <span class="page-link">...</span>
          </li>
        <?php } ?>

      <?php } ?>

      <?php if ($pager->getPage() == $page) { ?>
        <li class="page-item active d-none d-sm-block" aria-current="page">
          <span class="page-link"><?php echo $page; ?></span>
        </li>
      <?php } else { ?>
        <li class="page-item d-none d-sm-block">
          <?php echo link_to(
            $page,
            ['page' => $page] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
            ['class' => 'page-link', 'title' => __('Go to page %1%', ['%1%' => $page])]
          ); ?>
        </li>
      <?php } ?>

    <?php } ?>

    <?php if (floor($items / 2) < ($pager->getLastPage() - $pager->getPage())) { ?>
      <li class="page-item disabled dots d-none d-sm-block">
        <span class="page-link">...</span>
      </li>
      <li class="page-item d-none d-sm-block">
        <?php echo link_to(
          $pager->getLastPage(),
          ['page' => $pager->getLastPage()] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
          ['class' => 'page-link']
        ); ?>
      </li>
    <?php } ?>

    <?php if ($pager->getLastPage() > $pager->getPage()) { ?>
      <li class="page-item">
        <?php echo link_to(
          __('Next'),
          ['page' => $pager->getPage() + 1] + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
          ['class' => 'page-link']
        ); ?>
      </li>
    <?php } else { ?>
      <li class="page-item disabled">
        <a class="page-link" href="#" tabindex="-1" aria-disabled="true"><?php echo __('Next'); ?></a>
      </li>
    <?php } ?>

  </ul>

</nav>
<?php } ?>
