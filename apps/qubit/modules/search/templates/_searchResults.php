<div id="search-results">

  <div class="row">

    <div class="span12 hidden-phone">
      <h1>
        <?php echo __('%1% search results', array('%1%' => $pager->getNbResults())) ?>
        <?php if (sfConfig::get('app_multi_repository') && isset($pager->facets['repository_id'])): ?>
          <?php echo __('in %1% institutions', array('%1%' => count($pager->facets['repository_id']['terms']))) ?>
        <?php endif; ?>
      </h1>
    </div>

    <div id="phone-filter" class="span12 visible-phone">
      <h2 class="widebtn btn-huge" data-toggle="collapse" data-target="#facets, #top-facet">
        <?php echo __('Filter %1% Results', array('%1%' => $pager->getNbResults())) ?>
      </h2>
    </div>

  </div>

  <?php if (sfConfig::get('app_multi_repository') && isset($pager->facets['repository_id'])): ?>

    <div class="row">

      <div class="span12" id="top-facet">

        <h2 class="visible-phone widebtn btn-huge" data-toggle="collapse" data-target="#institutions"><?php echo __('Institutions') ?></h2>

        <div id="more-institutions" class="pull-right">

          <select>
            <option value=""><?php echo __('All institutions') ?></option>
            <?php foreach ($pager->facets['repository_id']['terms'] as $id => $term): ?>
              <option value="<?php echo $id; ?>"><?php echo __($term['term']) ?></option>
            <?php endforeach; ?>
          </select>

        </div>

      </div>

    </div>

  <?php endif; ?>

  <div class="row">

    <div class="span3" id="facets">

      <?php if (isset($pager->facets['subjects_id'])): ?>

        <?php echo get_partial('search/facet', array(
          'target' => '#facet-subject',
          'label' => __('Subject'),
          'facet' => 'subjects_id',
          'pager' => $pager,
          'filters' => $filters)) ?>

      <?php endif; ?>

      <?php if (isset($pager->facets['digitalObject_mediaTypeId'])): ?>

        <?php echo get_partial('search/facet', array(
          'target' => '#facet-mediatype',
          'label' => __('Media type'),
          'facet' => 'digitalObject_mediaTypeId',
          'pager' => $pager,
          'filters' => $filters)) ?>

      <?php endif; ?>

      <?php if (isset($pager->facets['places_id'])): ?>

        <?php echo get_partial('search/facet', array(
          'target' => '#facet-place',
          'label' => __('Place'),
          'facet' => 'places_id',
          'pager' => $pager,
          'filters' => $filters)) ?>

      <?php endif; ?>

      <?php if (isset($pager->facets['names_id'])): ?>

        <?php echo get_partial('search/facet', array(
          'target' => '#facet-name',
          'label' => __('Name'),
          'facet' => 'names_id',
          'pager' => $pager,
          'filters' => $filters)) ?>

      <?php endif; ?>

      <div class="section">

        <h2 class="visible-phone widebtn btn-huge" data-toggle="collapse" data-target="#dates"><?php echo __('Creation date') ?></h2>
        <h2 class="hidden-phone"><?php echo __('Creation date') ?></h2>

        <div class="scrollable dates" id="dates">

          <form method="get" action="<?php echo url_for($sf_request->getParameterHolder()->getAll()) ?>">
            <input type="hidden" name="query" value="<?php echo esc_entities($sf_request->query) ?>" />
            <input type="hidden" name="realm" value="<?php echo esc_entities($sf_request->realm) ?>" />
            <input type="text" value="<?php echo $pager->facets['dates_startDate']['min'] ?>" name="creationDate_from" />
            <span>-</span>
            <?php if (isset($pager->facets['dates_endDate'])): ?>
              <input type="text" value="<?php echo $pager->facets['dates_endDate']['max'] ?>" name="creationDate_to" />
            <?php else: ?>
              <input type="text" value="<?php echo $pager->facets['dates_startDate']['max'] ?>" name="creationDate_to" />
            <?php endif; ?>

          </form>

        </div>

      </div>

    </div>

    <div class="span9" id="content">

      <div class="listings">

        <?php if (isset($pager->facets['digitalObject_mediaTypeId'])): ?>

          <?php $numResults = 0 ?>
          <?php foreach ($pager->facets['digitalObject_mediaTypeId']['terms'] as $mediaType): ?>
            <?php $numResults += $mediaType['count']; ?>
          <?php endforeach; ?>

          <?php if ($numResults > 0): ?>
            <div class="result media">
              <h3><a href="#"><?php echo __('%1% results with digital media', array('%1%' => $numResults)) ?> <strong><?php echo __('Show all') ?></strong></a></h3>
            </div>
          <?php endif; ?>

        <?php endif; ?>

        <?php foreach ($pager->getResults() as $hit): ?>
          <?php $doc = build_i18n_doc($hit, array('creators')) ?>
          <?php echo include_partial('search/searchResult', array('doc' => $doc, 'pager' => $pager)) ?>
        <?php endforeach; ?>

        <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

      </div>

    </div>

  </div>

</div>

