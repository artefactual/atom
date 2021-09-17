<?php decorate_with('layout_1col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Browse donors'); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-flex flex-wrap gap-2 mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search donors'),
        'landmarkLabel' => __('Donor'),
    ]); ?>

    <div class="d-flex flex-wrap gap-2 ms-auto">
      <?php echo get_partial('default/sortPickers', ['options' => [
          'alphabetic' => __('Name'),
          'lastUpdated' => __('Date modified'),
          'identifier' => __('Identifier'),
      ]]); ?>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th>
          <?php if ('alphabetic' != $sf_request->sort) { ?>
            <th>
              <?php echo __('Updated'); ?>
            </th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pager->getResults() as $item) { ?>
          <tr>
            <td>
              <?php echo link_to(render_title($item), [$item, 'module' => 'donor']); ?>
            </td>
            <?php if ('alphabetic' != $sf_request->sort) { ?>
              <td>
                <?php echo format_date($item->updatedAt, 'f'); ?>
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
    <?php echo link_to(__('Add new'), ['module' => 'donor', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
  </section>

<?php end_slot(); ?>
