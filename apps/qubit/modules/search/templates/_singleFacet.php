<div class="btn-group top-facet">

  <?php echo link_to(
    __('All'),
    array(
      $facet => null,
      'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
    array('class' => 'btn' . ('' == $sf_request->getParameter($facet) ? ' active' : ''))) ?>

  <?php if (isset($pager->facets[$facet])): ?>

    <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>

      <?php $active = in_array($id, (array)@$filters[$facet]) ? true : false; ?>

      <?php echo link_to(
        __($term['term']).'<span class="badge">'.$term['count'].'</span>',
        array(
          $facet => (
            @$filters[$facet]
              ?
                implode(',', array_diff(
                  array_merge(@$filters[$facet], array($id)),
                  array_intersect(@$filters[$facet], array($id))))
              :
                $id),
          'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
        array('class' => 'btn' . ($active ? ' active' : ''))) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</div>
