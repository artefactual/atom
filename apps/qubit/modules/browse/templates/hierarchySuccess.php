<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <input type="button" id="fullwidth-treeview-reset-button" class="c-btn c-btn-submit" value="<?php echo __('Reset'); ?>" />
  <input type="button" id="fullwidth-treeview-more-button" class="c-btn c-btn-submit" data-label="<?php echo __('%1% more'); ?>" value="" />
  <?php echo image_tag('/vendor/jstree/themes/default/throbber.gif', ['id' => 'fullwidth-treeview-activity-indicator', 'alt' => __('Loading ...')]); ?>
  <h1><?php echo __('Hierarchy'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

<div id='main-column'></div>
<span id="fullwidth-treeview-configuration" data-items-per-page="<?php echo $itemsPerPage; ?>"></span>

<?php end_slot(); ?>
