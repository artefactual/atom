<?php if (isset($sf_request->$facet) || (isset($open) && $open
  && isset($pager->facets[$facet]) && 0 < count($pager->facets[$facet]['terms']))): ?>
  <section class="facet open">
<?php else: ?>
  <section class="facet">
<?php endif; ?>

  <div class="facet-header">
    <?php if (isset($sf_request->$facet) || (isset($open) && $open
      && isset($pager->facets[$facet]) && 0 < count($pager->facets[$facet]['terms']))): ?>
      <h3><a href="#" aria-expanded="true"><?php echo $label ?></a></h3>
    <?php else: ?>
      <h3><a href="#" aria-expanded="false"><?php echo $label ?></a></h3>
    <?php endif; ?>
  </div>

  <div class="facet-body" id="<?php echo $target ?>">

    <ul>
      <?php if ($facet === 'levels'): ?>
        <div class="lod-filter btn-group" data-toggle="buttons">
          <li>
            <label>
              <input type="radio" name="lod-filter" data-link="<?php echo $topLvlDescUrl ?>" <?php echo $checkedTopDesc ?>>
              <?php echo __('Top-level descriptions') ?>
            </label>
            <label>
              <input type="radio" name="lod-filter" data-link="<?php echo $allLvlDescUrl ?>" <?php echo $checkedAllDesc ?>>
              <?php echo __('All descriptions') ?>
            </label>
          </li>
        </div>
      <?php endif; ?>

      <?php $filters = sfOutputEscaper::unescape($filters) ?>

      <?php if (!isset($filters[$facet])): ?>
        <li class="active">
      <?php else: ?>
        <li>
      <?php endif; ?>
        <?php echo link_to(__('All'), array(
          $facet => null,
          'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
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
                'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(), array('aria-describedby' => 'facet-count-'.$id)) ?>
            <span class="facet-count" id="facet-count-<?php echo $id ?>"><?php echo $term['count'] ?><span><?php echo __('results') ?></span></span>
          </li>
        <?php endforeach; ?>
      <?php endif; ?>

    </ul>

  </div>

</section>
