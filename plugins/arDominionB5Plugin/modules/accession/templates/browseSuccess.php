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
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Accession number'); ?>
          </th>
          <th>
            <?php echo __('Title'); ?>
          </th>
          <th>
            <?php echo __('Acquisition date'); ?>
          </th>
          <?php if ('lastUpdated' == $sf_request->sort) { ?>
            <th>
              <?php echo __('Updated'); ?>
            </th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pager->getResults() as $hit) { ?>
          <?php $doc = $hit->getData(); ?>
          <tr>
            <td width="20%">
              <?php echo link_to($doc['identifier'], ['module' => 'accession', 'slug' => $doc['slug']]); ?>
            </td>
            <td>
              <?php echo link_to(render_title(get_search_i18n($doc, 'title')), ['module' => 'accession', 'slug' => $doc['slug']]); ?>
            </td>
            <td width="20%">
              <?php echo format_date($doc['date'], 'i'); ?>
            </td>
            <?php if ('lastUpdated' == $sf_request->sort) { ?>
              <td width="20%">
                <?php echo format_date($doc['updatedAt'], 'f'); ?>
              </td>
            <?php } ?>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

  <section class="actions mb-3">
    <?php echo link_to(__('Add new'), ['module' => 'accession', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
  </section>

<?php end_slot(); ?>
