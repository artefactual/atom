<?php if (isset($pager->facets[$facet]) && count($pager->facets[$facet]['terms']) > 2): ?>

  <?php if (isset($sf_request->$facet)): ?>
    <section class="facet open">
  <?php else: ?>
    <section class="facet">
  <?php endif; ?>

    <div class="facet-header">
      <?php if (isset($sf_request->$facet)): ?>
        <h3><a href="#" aria-expanded="true"><?php echo $label ?></a></h3>
      <?php else: ?>
        <h3><a href="#" aria-expanded="false"><?php echo $label ?></a></h3>
      <?php endif; ?>
    </div>

    <div class="facet-body" id="<?php echo $target ?>">

      <ul>

        <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>
          <?php if (($id == 'unique' && !isset($filters[$facet]))
          || (isset($filters[$facet]) && $id == $filters[$facet])): ?>
            <li class="active">
          <?php else: ?>
            <li>
          <?php endif; ?>
            <?php echo link_to(
              $term['term'] . '<span>, ' . $term['count'] . ' ' . __('results') . '</span>',
              array(
                $facet => $id == 'unique' ? null : $id,
                'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => '')) ?>
            <span class="facet-count" aria-hidden="true"><?php echo $term['count'] ?></span>
          </li>
        <?php endforeach; ?>

      </ul>

    </div>

  </section>

<?php endif; ?>
