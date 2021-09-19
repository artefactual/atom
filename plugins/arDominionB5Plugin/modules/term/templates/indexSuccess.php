<?php decorate_with('layout_3col'); ?>
<?php use_helper('Text'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_partial('term/sidebar', [
      'resource' => $resource,
      'showTreeview' => $addBrowseElements,
      'search' => $search,
      'aggs' => $aggs,
      'listPager' => $listPager, ]); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo render_title($resource); ?></h1>

  <?php echo get_component('term', 'navigateRelated', ['resource' => $resource]); ?>

  <?php echo get_partial('term/errors', ['errorSchema' => $errorSchema]); ?>

  <?php if (QubitTerm::ROOT_ID != $resource->parentId) { ?>
    <?php echo include_partial('default/breadcrumb', ['resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft')]); ?>
  <?php } ?>

  <?php echo get_component('default', 'translationLinks', ['resource' => $resource]); ?>

<?php end_slot(); ?>

<?php slot('context-menu'); ?>

  <nav>

    <?php echo get_partial('term/format', ['resource' => $resource]); ?>

    <?php if ($addBrowseElements) { ?>
      <?php echo get_partial('term/rightContextMenu', ['resource' => $resource, 'results' => $pager->getNbResults()]); ?>
    <?php } ?>

  </nav>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div id="content">
    <?php echo render_b5_section_heading(
        __('Elements area'),
        QubitAcl::check($resource, 'update'),
        [$resource, 'module' => 'term', 'action' => 'edit'],
        ['anchor' => 'elements-collapse', 'class' => 'rounded-top']
    ); ?>

    <?php echo get_partial('term/fields', ['resource' => $resource]); ?>
  </div>

  <?php echo get_partial('term/actions', ['resource' => $resource]); ?>

  <?php if ($addBrowseElements) { ?>
    <h1>
      <?php echo __('%1% %2% results for %3%', [
          '%1%' => $pager->getNbResults(),
          '%2%' => sfConfig::get('app_ui_label_informationobject'),
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
              [$resource, 'module' => 'term']
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
            'alphabetic' => __('Title'),
            'referenceCode' => __('Reference code'),
            'date' => __('Start date'),
        ]]); ?>
      </div>
    </div>

    <div id="content">
      <?php echo get_partial('term/directTerms', [
          'resource' => $resource,
          'aggs' => $aggs,
      ]); ?>

      <?php echo get_partial('search/searchResults', ['pager' => $pager, 'culture' => $culture]); ?>
    </div>
  <?php } ?>

<?php end_slot(); ?>

<?php if ($addBrowseElements) { ?>
  <?php slot('after-content'); ?>
    <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
  <?php end_slot(); ?>
<?php } ?>
