<section>

  <div class="facet-header">

    <div class="hidden-phone">
      <p><?php echo $label ?></p>
    </div>

    <div class="visible-phone">
      <button class="w-btn" data-toggle="collapse" data-target="<?php echo $target ?>"><?php echo $label ?></button>
    </div>

  </div>

  <div class="facet-body" id="<?php echo $target ?>">

    <ul>

      <li <?php if ('' == $sf_request->getParameter($facet)) echo 'class="active"' ?>>
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
            <span><?php echo $term['count'] ?></span>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>

    </ul>

  </div>

</section>
