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

      <?php if (!isset($filters[$facet])): ?>
        <li class="active">
      <?php else: ?>
        <li>
      <?php endif; ?>
        <?php echo link_to(__('Unique records'), array(
          $facet => null,
          'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
        <span class="facet-count"><?php echo $pager->facets[$facet]['terms']['unique']['count'] ?></span>
      </li>

      <?php if (isset($pager->facets[$facet])): ?>
        <?php foreach ($pager->facets[$facet]['terms'] as $id => $term): ?>
          <?php if ($id != 'unique'): ?>
            <li <?php if (isset($filters[$facet]) && $id == $filters[$facet]) echo 'class="active"' ?>>
              <?php echo link_to(
                $term['term'],
                array(
                  $facet => $id,
                  'page' => null) + $sf_data->getRaw('sf_request')->getParameterHolder()->getAll()) ?>
              <span class="facet-count"><?php echo $term['count'] ?></span>
            </li>
          <?php endif; ?>
        <?php endforeach; ?>
      <?php endif; ?>

    </ul>

  </div>

</section>
