<table class="table table-bordered sticky-enabled">
  <thead class="tableheader-processed">
    <tr>
      <th class="sortable" style="width: 40%">
        <?php echo link_to(__('Name'), array('sort' => ('nameUp' == $sf_request->sort) ? 'nameDown' : 'nameUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                           array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('nameUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif', array('alt' => __('Sort ascending'))) ?>
        <?php elseif ('nameDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif', array('alt' => __('Sort descending'))) ?>
        <?php endif; ?>
      </th>

      <th class="sortable" style="width: 20%">
        <?php echo link_to(__('Region'), array('sort' => ('regionUp' == $sf_request->sort) ? 'regionDown' : 'regionUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                           array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('regionUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif', array('alt' => __('Sort ascending'))) ?>
        <?php elseif ('regionDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif', array('alt' => __('Sort descending'))) ?>
        <?php endif; ?>
      </th>

      <th class="sortable" style="width: 20%">
        <?php echo link_to(__('Locality'), array('sort' => ('localityUp' == $sf_request->sort) ? 'localityDown' : 'localityUp') +
                           $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                           array('title' => __('Sort'), 'class' => 'sortable')) ?>

        <?php if ('localityUp' == $sf_request->sort): ?>
          <?php echo image_tag('up.gif', array('alt' => __('Sort ascending'))) ?>
        <?php elseif ('localityDown' == $sf_request->sort): ?>
          <?php echo image_tag('down.gif', array('alt' => __('Sort descending'))) ?>
        <?php endif; ?>
      </th>

      <th style="width: 17%">
        <?php echo __('Thematic area') ?>
      </th>

      <th style="width 3%">
      </th>
    </tr>
  </thead>

  <?php foreach ($pager->getResults() as $hit): ?>
    <?php $doc = $hit->getData() ?>
    <tr>
      <td>
        <?php if (isset($doc['logoPath'])): ?>
          <?php echo image_tag($doc['logoPath'], array('height' => '10%', 'width' => '10%', 'alt' => '')) ?>
        <?php endif; ?>

        <?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName', array('allowEmpty' => false,
                           'culture' => $selectedCulture))), array('module' => 'repository', 'slug' => $doc['slug'])) ?>
      </td>

      <td>
        <?php echo get_search_i18n($doc, 'region', array('allowEmpty' => true, 'culture' => $selectedCulture, 'cultureFallback' => true)) ?>
      </td>
      <td>
        <?php echo get_search_i18n($doc, 'city', array('allowEmpty' => true, 'culture' => $selectedCulture, 'cultureFallback' => true)) ?>
      </td>

      <td>
        <?php if (isset($doc['thematicAreas'])): ?>
          <?php foreach ($doc['thematicAreas'] as $areaTerm): ?>
            <li><?php echo render_value(QubitTerm::getById($areaTerm)) ?></li>
          <?php endforeach; ?>
        <?php endif; ?>
      </td>

      <td>
        <?php echo get_component('object', 'clipboardButton', array('slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true)) ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
