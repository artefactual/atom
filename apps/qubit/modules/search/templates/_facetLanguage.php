<?php if (isset($sf_request->$facet)): ?>
  <section class="facet open">
<?php else: ?>
  <section class="facet">
<?php endif; ?>

  <div class="facet-header">
    <p><?php echo $label ?></p>
  </div>

  <div class="facet-body" id="<?php echo $target ?>">

    <ul>

      <?php if (isset($pager->facets[$facet])): ?>
        <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>
          <li <?php if ($id == $filters[$facet]) echo 'class="active"' ?>>
            <?php echo link_to(
              $term['term'],
              array(
                $facet => $id,
                'page' => null) + $sf_request->getParameterHolder()->getAll()) ?>
            <span class="facet-count"><?php echo $term['count'] ?></span>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>

    </ul>

  </div>

</section>
