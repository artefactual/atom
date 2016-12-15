<?php decorate_with('layout_1col') ?>

<?php slot('title') ?>
  <?php echo get_partial('default/printPreviewBar') ?>

  <div class="multiline-header">
    <?php echo image_tag('/images/icons-large/icon-archival.png', array('alt' => '')) ?>
    <h1 aria-describedby="results-label"><?php echo __('Showing %1% results', array('%1%' => $pager->getNbResults())) ?></h1>
    <span class="sub" id="results-label"><?php echo __('Clipboard') ?></span>
  </div>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <section class="browse-options">
    <?php echo get_partial('default/printPreviewButton', array('class' => 'clipboard-print')) ?>
    <?php echo get_partial('default/sortPicker', array(
      'options' => array(
        'lastUpdated'   => __('Most recent'),
        'alphabetic'    => __('Alphabetic'),
        'relevance'     => __('Relevance'),
        'identifier'    => __('Identifier'),
        'referenceCode' => __('Reference code'),
        'startDate'     => __('Start date'),
        'endDate'       => __('End date')))) ?>
    &nbsp;
    <?php echo get_partial('default/genericPicker', array(
      'options' => array(
        'informationObject' => sfConfig::get('app_ui_label_informationobject'),
        'actor'             => sfConfig::get('app_ui_label_actor'),
        'repository'        => sfConfig::get('app_ui_label_repository')
      ),
      'label' => __('Entity type'),
      'param' => 'type')) ?>
  </section>
<?php end_slot() ?>

<?php slot('content') ?>
  <div id="content">
    <?php foreach ($pager->getResults() as $hit): ?>
      <?php if ('QubitInformationObject' === $entityType): ?>
        <?php echo get_partial('search/searchResult', array('hit' => $hit, 'culture' => $selectedCulture)) ?>
      <?php else: ?>
        <?php echo get_partial('actor/searchResult', array('doc' => $hit->getData(), 'culture' => $selectedCulture)) ?>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <?php echo get_partial('default/pager', array('pager' => $pager)) ?>

  <section class="actions">
    <ul>
      <li><?php echo link_to (__('Clear all'), array('module' => 'user', 'action' => 'clipboardClear'), array('class' => 'c-btn c-btn-delete')) ?></li>
      <?php if (isset($pager) && $pager->hasResults() && $sf_user->isAuthenticated()): ?>
        <li><?php echo link_to(__('Export'), array('module' => 'object', 'action' => 'export'), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php end_slot() ?>
