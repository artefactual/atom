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
    <?php if ($pager->hasResults()): ?>
      <?php echo get_partial('default/sortPicker', array('options' => $sortOptions)) ?>
      &nbsp;
    <?php endif; ?>
    <?php echo get_partial('default/genericPicker', array(
      'options' => $uiLabels,
      'label' => __('Entity type'),
      'param' => 'type')) ?>
  </section>
<?php end_slot() ?>

<?php slot('content') ?>
  <div id="content">
    <div class="text-section">
      <?php if (!isset($pager) || !$pager->hasResults()): ?>
        <?php echo __('No results for this entity type.') ?>
      <?php endif; ?>
    </div>

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
      <li><?php echo link_to(__('Clear %1 clipboard', array('%1' => lcfirst($uiLabels[$type]))), array('module' => 'user', 'action' => 'clipboardClear', 'type' => $entityType), array('class' => 'c-btn c-btn-delete')) ?></li>
      <?php if (isset($pager) && $pager->hasResults()): ?>
        <li><?php echo link_to(__('Export'), array('module' => 'object', 'action' => 'export', 'objectType' => $type), array('class' => 'c-btn')) ?></li>
      <?php endif; ?>
    </ul>
  </section>
<?php end_slot() ?>
