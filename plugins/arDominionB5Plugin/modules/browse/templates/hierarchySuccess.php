<?php decorate_with('layout_1col.php'); ?>
<?php slot('title'); ?>

  <h1>
    <?php echo __('Hierarchy'); ?>
    <span id="fullwidth-treeview-activity-indicator">
      <i class="fas fa-spinner fa-spin ms-2" aria-hidden="true"></i>
      <span class="visually-hidden"><?php echo __('Loading ...'); ?></span>
    </span>
  </h1>

  <div class="d-flex flex-wrap gap-2 mb-3">
    <input type="button" id="fullwidth-treeview-reset-button" class="btn atom-btn-white" value="<?php echo __('Reset'); ?>" />
    <input type="button" id="fullwidth-treeview-more-button" class="btn atom-btn-white" data-label="<?php echo __('%1% more'); ?>" value="" />
  </div>

<?php end_slot(); ?>

<?php slot('content'); ?>

<div id='main-column'>
  <span id="fullwidth-treeview-configuration" data-items-per-page="<?php echo $itemsPerPage; ?>"></span>
</div>

<?php end_slot(); ?>
