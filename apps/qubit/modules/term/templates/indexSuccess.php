<?php decorate_with('layout_3col') ?>
<?php use_helper('Text') ?>

<?php slot('sidebar') ?>
  <div class="sidebar-lowering-sort">

    <?php if (!$addBrowseElements): ?>
      <?php echo get_component('term', 'treeView', array('browser' => false)) ?>
    <?php else: ?>

      <?php echo get_component('term', 'treeView', array('browser' => false, 'tabs' => true, 'pager' => $listPager)) ?>

      <section id="facets">

        <div class="visible-phone facets-header">
          <a class="x-btn btn-wide">
            <i class="fa fa-filter"></i>
            <?php echo __('Filters') ?>
          </a>
        </div>

        <div class="content">

          <?php echo get_partial('search/aggregation', array(
            'id' => '#facet-languages',
            'label' => __('Language'),
            'name' => 'languages',
            'aggs' => $aggs,
            'filters' => $search->filters)) ?>

          <?php if (QubitTaxonomy::PLACE_ID != $resource->taxonomyId): ?>
            <?php echo get_partial('search/aggregation', array(
              'id' => '#facet-places',
              'label' => sfConfig::get('app_ui_label_place'),
              'name' => 'places',
              'aggs' => $aggs,
              'filters' => $search->filters)) ?>
            <?php endif; ?>

          <?php if (QubitTaxonomy::SUBJECT_ID != $resource->taxonomyId): ?>
            <?php echo get_partial('search/aggregation', array(
              'id' => '#facet-subjects',
              'label' => sfConfig::get('app_ui_label_subject'),
              'name' => 'subjects',
              'aggs' => $aggs,
              'filters' => $search->filters)) ?>
          <?php endif; ?>

          <?php if (QubitTaxonomy::GENRE_ID != $resource->taxonomyId): ?>
            <?php echo get_partial('search/aggregation', array(
              'id' => '#facet-genres',
              'label' => sfConfig::get('app_ui_label_genre'),
              'name' => 'genres',
              'aggs' => $aggs,
              'filters' => $search->filters)) ?>
          <?php endif; ?>

        </div>

      </section>

    <?php endif; ?>

 </div>
<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($resource) ?></h1>

  <?php if (isset($errorSchema)): ?>
    <div class="messages error">
      <ul>
        <?php foreach ($errorSchema as $error): ?>
          <li><?php echo $error ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <?php if (QubitTerm::ROOT_ID != $resource->parentId): ?>
    <?php echo include_partial('default/breadcrumb', array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>
  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>
<?php end_slot() ?>

<?php slot('context-menu') ?>

  <div class="sidebar">
    <?php echo get_partial('term/format', array('resource' => $resource)) ?>

    <?php if ($addBrowseElements): ?>
      <?php echo get_partial('term/rightContextMenu', array('resource' => $resource, 'results' => $pager->getNbResults())) ?>
    <?php endif; ?>
  </div>

<?php end_slot() ?>

<?php slot('content') ?>

  <div id="content">

    <?php echo render_show(__('Taxonomy'), link_to(render_title($resource->taxonomy), array($resource->taxonomy, 'module' => 'taxonomy'))) ?>

    <div class="field">
      <h3><?php echo __('Code') ?></h3>
      <div>
        <?php echo $resource->code ?>
        <?php if (!empty($resource->code) && QubitTaxonomy::PLACE_ID == $resource->taxonomy->id): ?>
          <?php echo image_tag('https://maps.googleapis.com/maps/api/staticmap?zoom=13&size=300x300&sensor=false&center='.$resource->code,
            array('class' => 'static-map', 'alt' => __('Map of %1%', array('%1%' => esc_entities(render_title(truncate_text($resource, 100))))))) ?>
        <?php endif; ?>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Scope note(s)') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::SCOPE_NOTE_ID)) as $item): ?>
            <?php if ($item->sourceCulture != $sf_user->getCulture()): ?>
              <?php continue; ?>
            <?php endif; ?>
            <li><?php echo render_value($item->getContent(array('cultureFallback' => true))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Source note(s)') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::SOURCE_NOTE_ID)) as $item): ?>
            <li><?php echo render_value($item->getContent(array('cultureFallback' => true))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Display note(s)') ?></h3>
      <div>
        <ul>
          <?php foreach ($resource->getNotesByType(array('noteTypeId' => QubitTerm::DISPLAY_NOTE_ID)) as $item): ?>
            <li><?php echo render_value($item->getContent(array('cultureFallback' => true))) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Hierarchical terms') ?></h3>
      <div>

        <?php if (QubitTerm::ROOT_ID != $resource->parent->id): ?>
          <?php echo render_show(render_title($resource), __('BT %1%', array('%1%' => link_to(render_title($resource->parent), array($resource->parent, 'module' => 'term'))))) ?>
        <?php endif; ?>

        <div class="field">
          <h3><?php echo render_title($resource) ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->getChildren(array('sortBy' => 'name')) as $item): ?>
                <li><?php echo __('NT %1%', array('%1%' => link_to(render_title($item), array($item, 'module' => 'term')))) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <div class="field">
      <h3><?php echo __('Equivalent terms') ?></h3>
      <div>

        <div class="field">
          <h3><?php echo render_title($resource) ?></h3>
          <div>
            <ul>
              <?php foreach ($resource->otherNames as $item): ?>
                <?php if ($item->sourceCulture != $sf_user->getCulture()): ?>
                  <?php continue; ?>
                <?php endif; ?>
                <li><?php echo __('UF %1%', array('%1%' => render_title($item))) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

      </div>
    </div>

    <?php if (0 < count($converseTerms = QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::CONVERSE_TERM_ID)))): ?>
      <?php echo render_show(__('Converse term'), link_to(render_title($converseTerms[0]->getOpposedObject($resource)), array($converseTerms[0]->getOpposedObject($resource), 'module' => 'term'))) ?>
    <?php endif; ?>

    <div class="field">
      <h3><?php echo __('Associated terms') ?></h3>
      <div>

        <div class="field">
          <h3><?php echo render_title($resource) ?></h3>
          <div>
            <ul>
              <?php foreach (QubitRelation::getBySubjectOrObjectId($resource->id, array('typeId' => QubitTerm::TERM_RELATION_ASSOCIATIVE_ID)) as $item): ?>
                <li><?php echo __('RT %1%', array('%1%' => link_to(render_title($item->getOpposedObject($resource->id)), array($item->getOpposedObject($resource->id), 'module' => 'term')))) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>

      </div>
    </div>
  </div>

  <section class="actions">

    <ul>

      <?php if ((QubitAcl::check($resource, 'update') || QubitAcl::check($resource, 'translate')) && !QubitTerm::isProtected($resource->id)): ?>
        <li><?php echo link_to (__('Edit'), array($resource, 'module' => 'term', 'action' => 'edit'), array('class' => 'c-btn c-btn-submit')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource, 'delete') && !QubitTerm::isProtected($resource->id)): ?>
        <li><?php echo link_to (__('Delete'), array($resource, 'module' => 'term', 'action' => 'delete'), array('class' => 'c-btn c-btn-delete')) ?></li>
      <?php endif; ?>

      <?php if (QubitAcl::check($resource->taxonomy, 'createTerm')): ?>
        <li><?php echo link_to(__('Add new'), array('module' => 'term', 'action' => 'add', 'parent' => url_for(array($resource, 'module' => 'term')), 'taxonomy' => url_for(array($resource->taxonomy, 'module' => 'taxonomy'))), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>

    </ul>

  </section>

  <?php if ($addBrowseElements): ?>
    <h1><?php echo __('%1% Results for %2%', array('%1%' => $pager->getNbResults(), '%2%' => render_title($resource))) ?></h1>

    <section class="header-options">

          <?php if (isset($sf_request->onlyDirect)): ?>
            <span class="search-filter">
              <?php echo __('Only results directly related') ?>
              <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
              <?php unset($params['onlyDirect']) ?>
              <?php unset($params['page']) ?>
              <a href="<?php echo url_for(array($resource, 'module' => 'term') + $params) ?>" class="remove-filter"><i class="fa fa-times"></i></a>
            </span>
          <?php endif; ?>

        <div id="sort-header">
          <?php echo get_partial('default/sortPicker', array(
            'options' => array(
              'lastUpdated' => __('Most recent'),
              'alphabetic'  => __('Alphabetic'),
              'referenceCode'  => __('Reference code'),
              'date'        => __('Date')))) ?>
        </div>

    </section>

    <div id="content">

      <?php if (!isset($sf_request->onlyDirect) && isset($aggs['direct']) && 0 < $aggs['direct']['doc_count']): ?>
        <div class="search-result media-summary">
          <p>
            <?php echo __('%1% results directly related', array(
              '%1%' => $aggs['direct']['doc_count'])) ?>
            <?php $params = $sf_data->getRaw('sf_request')->getGetParameters() ?>
            <?php unset($params['page']) ?>
            <a href="<?php echo url_for(array($resource, 'module' => 'term') + $params + array('onlyDirect' => true)) ?>">
              <i class="fa fa-search"></i>
              <?php echo __('Exclude narrower terms') ?>
            </a>
          </p>
        </div>
      <?php endif; ?>

      <?php echo get_partial('search/searchResults', array('pager' => $pager, 'culture' => $culture)) ?>

    </div>
  <?php endif; ?>

<?php end_slot() ?>

<?php if ($addBrowseElements): ?>
  <?php slot('after-content') ?>
    <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
  <?php end_slot() ?>
<?php endif; ?>
