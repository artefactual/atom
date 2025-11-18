<?php decorate_with('layout_3col'); ?>
<?php use_helper('Date'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_partial('term/sidebar', [
      'resource' => $resource,
      'showTreeview' => true,
      'search' => $search,
      'aggs' => $aggs,
      'listPager' => $listPager, ]); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

  <?php echo get_component('term', 'navigateRelated', ['resource' => $resource]); ?>

  <?php echo get_partial('term/errors', ['errorSchema' => $errorSchema]); ?>

  <?php if (QubitTerm::ROOT_ID != $resource->parentId) { ?>
    <?php echo include_partial('default/breadcrumb',
                 ['resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft')]); ?>
  <?php } ?>

<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>
<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <nav>

    <?php echo get_partial('term/format', ['resource' => $resource]); ?>

    <?php echo get_partial('term/rightContextMenu', ['resource' => $resource, 'results' => $pager->getNbResults()]); ?>

  </nav>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div id="content">
    <?php echo get_partial('term/fields', ['resource' => $resource]); ?>
  </div>

  <?php echo get_partial('term/actions', ['resource' => $resource]); ?>

  <h1>
    <?php echo __('%1% %2% results for %3%', [
        '%1%' => $pager->getNbResults(),
        '%2%' => sfConfig::get('app_ui_label_actor'),
        '%3%' => render_title($resource), ]);
    ?>
  </h1>

  <div class="d-flex flex-wrap gap-2">
    <?php if (isset($sf_request->onlyDirect)) { ?>
      <?php $params = $sf_data->getRaw('sf_request')->getGetParameters(); ?>
      <?php unset($params['onlyDirect']); ?>
      <?php unset($params['page']); ?>
      <a
        href="<?php echo url_for(
            [$resource, 'module' => 'term', 'action' => 'relatedAuthorities']
            + $params
        ); ?>"
        class="btn btn-sm atom-btn-white align-self-start mw-100 filter-tag d-flex">
        <span class="visually-hidden">
          <?php echo __('Remove filter:'); ?>
        </span>
        <span class="text-truncate d-inline-block">
          <?php echo __('Only results directly related'); ?>
        </span>
        <i aria-hidden="true" class="fas fa-times ms-2 align-self-center"></i>
      </a>
    <?php } ?>

    <div class="d-flex flex-wrap gap-2 ms-auto mb-3">
      <?php echo get_partial('default/sortPickers', ['options' => [
          'lastUpdated' => __('Date modified'),
          'alphabetic' => __('Name'),
          'identifier' => __('Identifier'),
      ]]); ?>
    </div>
  </div>

  <div id="content">

    <?php echo get_partial('term/directTerms', [
        'resource' => $resource,
        'aggs' => $aggs,
    ]); ?>

    <?php if ($pager->getNbResults()) { ?>

      <?php foreach ($pager->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <?php echo include_partial('actor/searchResult', ['doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture, 'clipboardType' => 'actor']); ?>
      <?php } ?>

    <?php } else { ?>

      <div class="p-3">
        <?php echo __('We couldn\'t find any results matching your search.'); ?>
      </div>

    <?php } ?>

  </div>

<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
