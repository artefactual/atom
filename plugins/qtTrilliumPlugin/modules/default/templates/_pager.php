<div class="result-count">
  <?php if (0 < $pager->getNbResults()): ?>
    <?php echo __('Results %1% to %2% of %3%', array('%1%' => $pager->getFirstIndice(), '%2%' => $pager->getLastIndice(), '%3%' => $pager->getNbResults())) ?>
  <?php else: ?>
    <?php echo __('No results') ?>
  <?php endif; ?>
</div>

<?php if ($pager->haveToPaginate()): ?>
  <div class="pager section">

    <h2 class="element-invisible"><?php echo __('Pages') ?></h2>

    <div class="content pagination">

      <ul>

        <?php $lft = isset($small) && true === $small ? '&laquo;' : __('&laquo; Previous'); ?>
        <?php $rgt = isset($small) && true === $small ? '&raquo;' : __('Next &raquo;'); ?>

        <?php if (1 < $pager->getPage()): ?>
          <li class="prev"><?php echo link_to($lft, array('page' => $pager->getPage() - 1) + $sf_request->getParameterHolder()->getAll(), array('rel' => 'prev', 'title' => __('Go to previous page'))) ?></li>
        <?php else: ?>
          <li class="prev disabled"><a href="#" onclick="return false;"><?php echo $lft ?></a></li>
        <?php endif; ?>

        <?php foreach ($pager->getLinks(10) as $page): ?>
          <?php if ($pager->getPage() == $page): ?>
            <li class="active"><a href="#"><?php echo $page ?></a></li>
          <?php else: ?>
            <li><?php echo link_to($page, array('page' => $page) + $sf_request->getParameterHolder()->getAll(), array('title' => __('Go to page %1%', array('%1%' => $page)))) ?></li>
          <?php endif; ?>
        <?php endforeach ?>

        <?php if ($pager->getLastPage() > $pager->getPage()): ?>
          <li class="next"><?php echo link_to($rgt, array('page' => $pager->getPage() + 1) + $sf_request->getParameterHolder()->getAll(), array('rel' => 'next', 'title' => __('Go to next page'))) ?></li>
        <?php else: ?>
          <li class="next disabled"><a href="#" onclick="return false;"><?php echo $rgt ?></a></li>
        <?php endif; ?>

      </ul>

    </div>

  </div>
<?php endif; ?>

<?php if (10 < $pager->getNbResults()): ?>
  <div class="itemsPerPage section">
    <?php ob_start() ?>
      <ul>
        <?php foreach (array(10, 50, 100, 500) as $limit): ?>

          <?php if ($sf_request->limit == $limit): ?>
            <li class="active"><?php echo $limit ?></li>
          <?php else: ?>
            <li><?php echo link_to($limit, array('limit' => $limit) + $sf_request->getParameterHolder()->getAll(), array('title' => __('%1% results per page', array('%1%' => $limit)))) ?></li>
          <?php endif; ?>

          <?php if ($pager->getNbResults() < $limit) break; ?>

        <?php endforeach; ?>
      </ul>
    <?php echo __('%1% results per page', array('%1%' => ob_get_clean())) ?>
  </div>
<?php endif; ?>
