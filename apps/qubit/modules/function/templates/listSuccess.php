<?php use_helper('Date'); ?>
<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('List %1%', ['%1%' => sfConfig::get('app_ui_label_function')]); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="d-inline-block mb-3">
    <?php echo get_component('search', 'inlineSearch', [
        'label' => __('Search %1%', ['%1%' => strtolower(sfConfig::get('app_ui_label_repository'))]),
    ]); ?>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th><th>
            <?php echo __('Updated'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pager->getResults() as $item) { ?>
          <tr>
            <td>
              <?php echo link_to(render_title($item), $item); ?>
            </td><td>
              <?php echo format_date($item->updatedAt, 'f'); ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
