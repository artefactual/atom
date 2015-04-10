<table class="table table-bordered sticky-enabled">
  <thead class="tableheader-processed">
    <tr>
      <th class="sortable" style="width: 40%">
        <?php echo link_to(__('Name'), array('sort' => ('nameUp' == $sf_request->sort) ? 'nameDown' : 'nameUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                           array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('nameUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif') ?>
        <?php elseif ('nameDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif') ?>
        <?php endif; ?>
      </th>

      <th class="sortable" style="width: 20%">
        <?php echo link_to(__('Region'), array('sort' => ('regionUp' == $sf_request->sort) ? 'regionDown' : 'regionUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                          array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('regionUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif') ?>
        <?php elseif ('regionDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif') ?>
        <?php endif; ?>
      </th>

      <th class="sortable" style="width: 20%">
        <?php echo link_to(__('Locality'), array('sort' => ('localityUp' == $sf_request->sort) ? 'localityDown' : 'localityUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                          array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('localityUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif') ?>
        <?php elseif ('localityDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif') ?>
        <?php endif; ?>
      </th>

      <th style="width: 20%">
        <?php echo __('Thematic Area') ?>
      </th>
    </tr>
  </thead>

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $resource = QubitRepository::getById($hit->getId()); ?>
    <tr>

      <td>
        <?php if ($resource->existsLogo()): ?>
          <?php echo image_tag($resource->getLogoPath(), array('height' => '10%', 'width' => '10%')) ?>
        <?php endif; ?>
        <?php echo link_to(render_title($resource), array('module' => 'repository', 'slug' => $resource->slug)) ?>
      </td>

      <td><?php echo $resource->getRegion() ?></td>
      <td><?php echo $resource->getCity() ?></td>

      <td>
        <?php foreach ($resource->getTermRelations(QubitTaxonomy::THEMATIC_AREA_ID) as $item): ?>
          <li><?php echo __(render_value($item->term)) ?></li>
        <?php endforeach; ?>
      </td>

    </tr>
  <?php endforeach; ?>
</table>
