<?php if (isset($sf_request->$facet) || (isset($open) && $open
  && isset($pager->facets[$facet]) && 0 < count($pager->facets[$facet]['terms']))): ?>
  <section class="facet open">
<?php else: ?>
  <section class="facet">
<?php endif; ?>

  <div class="facet-header">
    <p><?php echo $label ?></p>
  </div>

  <div class="facet-body" id="<?php echo $target ?>">

    <ul>

      <?php if (!isset($filters[$facet])): ?>
        <li class="active">
      <?php else: ?>
        <li>
      <?php endif; ?>
        <?php echo link_to(__('All'), array(
          $facet => null,
          'page' => null) + $sf_request->getParameterHolder()->getAll()) ?>
      </li>

      <?php if (isset($pager->facets[$facet])): ?>
        <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>
          <li <?php if (in_array($id, (array)@$filters[$facet])) echo 'class="active"' ?>>
            <?php echo link_to(
              __($term['term']),
              array(
                $facet => (
                  @$filters[$facet]
                    ?
                      implode(',', array_diff(
                        array_merge(@$filters[$facet], array($id)),
                        array_intersect(@$filters[$facet], array($id))))
                    :
                      $id),
                'page' => null) + $sf_request->getParameterHolder()->getAll()) ?>
            <span class="facet-count"><?php echo $term['count'] ?></span>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>

    </ul>

  </div>

</section>
