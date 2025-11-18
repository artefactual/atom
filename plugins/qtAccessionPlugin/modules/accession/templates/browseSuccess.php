<?php decorate_with('layout_1col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Browse accessions'); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search accessions'),
        'landmarkLabel' => __('Accession'),
    ]); ?>

    <div class="d-flex flex-wrap gap-2 ms-auto">
      <?php echo get_partial('default/sortPickers', ['options' => $sortOptions]); ?>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
    <?php $canExportAccessions = $sf_user->hasCredential(['editor', 'administrator'], false); ?>

    <div class="d-flex flex-wrap gap-2 mb-3">
      <?php if ($sf_user->isAuthenticated() && $canExportAccessions && !isset($sf_request->subquery)) { ?>
        <a
          class="btn btn-sm atom-btn-white"
          href="<?php echo url_for(array_merge(
              $sf_data->getRaw('sf_request')->getParameterHolder()->getAll(),
              ['module' => 'accession', 'action' => 'exportCsv']
          )); ?>">
          <i class="fas fa-upload me-1" aria-hidden="true"></i>
          <?php echo __('Export CSV'); ?>
        </a>
      <?php } ?>
    </div>

    <div id="content">

      <?php foreach ($pager->getResults() as $hit) { ?>
        <?php $doc = $hit->getData(); ?>
        <?php echo include_partial('accession/searchResult', [
            'doc' => $doc,
            'pager' => $pager,
            'culture' => $selectedCulture,
            'clipboardType' => 'accession',
            'canExportAccessions' => $canExportAccessions,
        ]); ?>
      <?php } ?>

    </div>

<?php end_slot(); ?>

<?php slot('after-content'); ?>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <section class="actions mb-3">
    <?php echo link_to(__('Add new'), ['module' => 'accession', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
  </section>

<?php end_slot(); ?>
