<div class="btn-group top-facet">

  <?php echo link_to(
    __('All'),
    array(
      $facet => null,
      'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
    array('class' => 'btn' . ('' == $sf_data->getRaw('sf_request')->getParameter($facet) ? ' active' : ''))) ?>

  <?php if (isset($pager->facets[$facet])): ?>

    <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>

      <?php $filter = count($filters) > 0 ? $filters->getRaw($facet) : array() ?>
      <?php $active = in_array($id, $filter) ? true : false; ?>

      <?php echo link_to(
        __($term['term']).'<span class="badge">'.$term['count'].'</span>',
        array(
          $facet => (
            $filter
              ?
                implode(',', array_diff(
                  array_merge($filter, array($id)),
                  array_intersect($filter, array($id))))
              :
                $id),
          'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
        array('class' => 'btn' . ($active ? ' active' : ''))) ?>

    <?php endforeach; ?>

  <?php endif; ?>

</div>
