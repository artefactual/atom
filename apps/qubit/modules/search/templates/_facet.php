<?php if (isset($pager->facets[$facet]) && (isset($filters[$facet])
  || count($pager->facets[$facet]['terms']) > 1)): ?>

  <?php if (isset($sf_request->$facet) || (isset($open) && $open
    && 0 < count($pager->facets[$facet]['terms']))): ?>
    <section class="facet open">
  <?php else: ?>
    <section class="facet">
  <?php endif; ?>

    <div class="facet-header">
      <?php if (isset($sf_request->$facet) || (isset($open) && $open
        && 0 < count($pager->facets[$facet]['terms']))): ?>
        <h3><a href="#" aria-expanded="true"><?php echo $label ?></a></h3>
      <?php else: ?>
        <h3><a href="#" aria-expanded="false"><?php echo $label ?></a></h3>
      <?php endif; ?>
    </div>

    <div class="facet-body" id="<?php echo $target ?>">

      <?php $filters = sfOutputEscaper::unescape($filters) ?>

      <ul>

        <?php if (!isset($filters[$facet])): ?>
          <li class="active">
        <?php else: ?>
          <li>
        <?php endif; ?>
          <?php echo link_to(__('All'), array(
            $facet => null,
            'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => '')) ?>
          </li>

        <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>
          <li <?php if (isset($filters[$facet]) && in_array($id, (array)$filters[$facet])) echo 'class="active"' ?>>
            <?php echo link_to(
              __($term['term']) . '<span>, ' . $term['count'] . ' ' . __('results') . '</span>',
              array(
                $facet => (
                  isset($filters[$facet])
                    ?
                      implode(',', array_diff(
                        array_merge($filters[$facet], array($id)),
                        array_intersect($filters[$facet], array($id))))
                    :
                      $id),
                'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('title' => '')) ?>
            <span class="facet-count" aria-hidden="true"><?php echo $term['count'] ?></span>
          </li>
        <?php endforeach; ?>

      </ul>

    </div>

  </section>

<?php endif; ?>
