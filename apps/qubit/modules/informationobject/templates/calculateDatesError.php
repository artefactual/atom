<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Calculate dates - Error'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo $form->renderFormTag(url_for([
      $resource, 'module' => 'informationobject', 'action' => 'calculateDates', ]
  )); ?>
    <?php if (1 == $resource->rgt - $resource->lft || 0 == count($descendantEventTypes)) { ?>
      <div id="content" class="p-3">
        <?php if (1 == $resource->rgt - $resource->lft) { ?>
            <?php echo __(
                'Cannot calculate accumulated dates because this %1% has no children',
                ['%1%' => sfConfig::get('app_ui_label_informationobject')]
            ); ?>
        <?php } else { ?>
          <?php echo __('Cannot calculate accumulated dates because no lower level dates exist'); ?>
        <?php } ?>
      </div>
    <?php } ?>

    <section class="actions mb-3">
      <?php echo link_to(
          __('Cancel'),
          [$resource, 'module' => 'informationobject'],
          ['class' => 'btn atom-btn-outline-light', 'role' => 'button']
      ); ?>
    </section>
  </form>
<?php end_slot(); ?>
