<?php decorate_with('layout_3col') ?> 
<?php use_helper('Date') ?>

<?php slot('sidebar') ?>

  <?php echo get_partial('term/sidebar', array(
    'resource' => $resource,
    'showTreeview' => true,
    'search' => $search,
    'aggs' => $aggs,
    'listPager' => $listPager)) ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo render_title($resource) ?></h1>

  <?php echo get_component('term', 'navigateRelated', array('resource' => $resource)) ?>

  <?php echo get_partial('term/errors', array('errorSchema' => $errorSchema)) ?>

  <?php if (QubitTerm::ROOT_ID != $resource->parentId): ?>
    <?php echo include_partial('default/breadcrumb',
                 array('resource' => $resource, 'objects' => $resource->getAncestors()->andSelf()->orderBy('lft'))) ?>
  <?php endif; ?>

<?php end_slot() ?>

<?php slot('before-content') ?>
  <?php echo get_component('default', 'translationLinks', array('resource' => $resource)) ?>
<?php end_slot() ?>

<?php slot('context-menu') ?>

  <div class="sidebar">
    <?php echo get_partial('term/format', array('resource' => $resource)) ?>

    <?php echo get_partial('term/rightContextMenu', array('resource' => $resource, 'results' => $pager->getNbResults())) ?>
  </div>

<?php end_slot() ?>

<?php slot('content') ?>

  <div id="content">
    <?php echo get_partial('term/fields', array('resource' => $resource)) ?>
  </div>

  <?php echo get_partial('term/actions', array('resource' => $resource)) ?>

  <h1><?php echo __('%1% %2% results for %3%',
                   array('%1%' => $pager->getNbResults(), '%2%' => sfConfig::get('app_ui_label_actor'), '%3%' => render_title($resource))) ?></h1>

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

    <div class="pickers">
      <?php echo get_partial('default/sortPickers',
        array(
          'options' => array(
            'lastUpdated' => __('Date modified'),
            'alphabetic'  => __('Name'),
            'identifier'  => __('Identifier')))) ?>
    </div>

  </section>

  <div id="content">

    <?php echo get_partial('term/directTerms', array(
      'resource' => $resource,
      'aggs' => $aggs)) ?>

    <?php if ($pager->getNbResults()): ?>

      <?php foreach ($pager->getResults() as $hit): ?>
        <?php $doc = $hit->getData() ?>
        <?php echo include_partial('actor/searchResult', array('doc' => $doc, 'pager' => $pager, 'culture' => $selectedCulture, 'clipboardType' => 'actor')) ?>
      <?php endforeach; ?>

    <?php else: ?>

      <div>
        <h2><?php echo __('We couldn\'t find any results matching your search.') ?></h2>
      </div>

    <?php endif; ?>

  </div>

<?php end_slot() ?>

<?php slot('after-content') ?>
  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>
<?php end_slot() ?>
