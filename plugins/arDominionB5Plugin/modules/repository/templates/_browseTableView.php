<div class="table-responsive mb-3">
  <table class="table table-bordered mb-0">
    <thead>
      <tr>
        <th class="sortable" style="width: 40%">
          <?php echo link_to(__('Name'), ['sort' => ('nameUp' == $sf_request->sort) ? 'nameDown' : 'nameUp'] +
                            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                            ['title' => __('Sort'), 'class' => 'sortable']); ?>

          <?php if ('nameUp' == $sf_request->sort) { ?>
            <?php echo image_tag('up.gif', ['alt' => __('Sort ascending')]); ?>
          <?php } elseif ('nameDown' == $sf_request->sort) { ?>
            <?php echo image_tag('down.gif', ['alt' => __('Sort descending')]); ?>
          <?php } ?>
        </th>

        <th class="sortable" style="width: 20%">
          <?php echo link_to(__('Region'), ['sort' => ('regionUp' == $sf_request->sort) ? 'regionDown' : 'regionUp'] +
                            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                            ['title' => __('Sort'), 'class' => 'sortable']); ?>

          <?php if ('regionUp' == $sf_request->sort) { ?>
            <?php echo image_tag('up.gif', ['alt' => __('Sort ascending')]); ?>
          <?php } elseif ('regionDown' == $sf_request->sort) { ?>
            <?php echo image_tag('down.gif', ['alt' => __('Sort descending')]); ?>
          <?php } ?>
        </th>

        <th class="sortable" style="width: 20%">
          <?php echo link_to(__('Locality'), ['sort' => ('localityUp' == $sf_request->sort) ? 'localityDown' : 'localityUp'] +
                            $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
                            ['title' => __('Sort'), 'class' => 'sortable']); ?>

          <?php if ('localityUp' == $sf_request->sort) { ?>
            <?php echo image_tag('up.gif', ['alt' => __('Sort ascending')]); ?>
          <?php } elseif ('localityDown' == $sf_request->sort) { ?>
            <?php echo image_tag('down.gif', ['alt' => __('Sort descending')]); ?>
          <?php } ?>
        </th>

        <th style="width: 20%">
          <?php echo __('Thematic area'); ?>
        </th>

        <th>
          <span class="visually-hidden"><?php echo __('Clipboard'); ?></span>
        </th>
      </tr>
    </thead>

    <?php foreach ($pager->getResults() as $hit) { ?>
      <?php $doc = $hit->getData(); ?>
      <tr>
        <td>
          <?php if (isset($doc['logoPath'])) { ?>
            <p>
              <?php echo image_tag($doc['logoPath'], ['class' => 'img-thumbnail', 'width' => '100']); ?>
            </p>
          <?php } ?>

          <?php echo link_to(render_title(get_search_i18n($doc, 'authorizedFormOfName', ['allowEmpty' => false,
              'culture' => $selectedCulture, ])), ['module' => 'repository', 'slug' => $doc['slug']]); ?>
        </td>

        <td>
          <?php echo render_value_inline(get_search_i18n($doc, 'region', ['allowEmpty' => true, 'culture' => $selectedCulture, 'cultureFallback' => true])); ?>
        </td>
        <td>
          <?php echo render_value_inline(get_search_i18n($doc, 'city', ['allowEmpty' => true, 'culture' => $selectedCulture, 'cultureFallback' => true])); ?>
        </td>

        <td>
          <?php if (isset($doc['thematicAreas'])) { ?>
            <?php foreach ($doc['thematicAreas'] as $areaTerm) { ?>
              <li><?php echo render_value_inline(QubitTerm::getById($areaTerm)); ?></li>
            <?php } ?>
          <?php } ?>
        </td>

        <td>
          <?php echo get_component('clipboard', 'button', ['slug' => $doc['slug'], 'wide' => false, 'repositoryOrDigitalObjBrowse' => true, 'type' => 'repository']); ?>
        </td>
      </tr>
    <?php } ?>
  </table>
</div>
