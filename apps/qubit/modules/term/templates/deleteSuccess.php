<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo $form->renderGlobalErrors(); ?>
  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'term', 'action' => 'delete']), ['method' => 'delete']); ?>
    <?php echo $form->renderHiddenFields(); ?>

    <?php if (
        0 < $resource->getRelatedObjectCount()
        || 0 < count($resource->events)
        || 0 < $count
    ) { ?>
      <div id="content" class="p-3 pb-0">
        <?php if (0 < $resource->getRelatedObjectCount()) { ?>
          <?php echo __('This term is used in %1% records. The term will be deleted from these records.', ['%1%' => $resource->getRelatedObjectCount()]); ?>
          <div class="mb-3">
            <?php echo __('The related object(s) will <strong>not</strong> be deleted'); ?>
          </div>
        <?php } ?>

        <?php if (0 < count($resource->events)) { ?>
          <?php echo __('It\'s used in %1% events that will also be deleted', ['%1%' => count($resource->events)]); ?>
          <ul>
            <?php foreach ($resource->events as $item) { ?>
              <li><?php echo render_value_inline(Qubit::renderDateStartEnd($item->getDate(['cultureFallback' => true]), $item->startDate, $item->endDate)); ?> (<?php echo render_title($resource); ?>) <?php echo link_to(render_title($item->object), [$item->object, 'module' => 'informationobject']); ?></li>
            <?php } ?>
          </ul>
        <?php } ?>

        <?php if (0 < $count) { ?>
          <?php echo __('It has %1% descendants that will also be deleted', ['%1%' => $count]); ?>
          <ul>
            <?php foreach ($resource->descendants as $index => $item) { ?>
              <li><?php echo link_to(render_title($item), [$item, 'module' => 'term']); ?></li>
              <?php if ($index + 1 == $previewSize) { ?>
                <?php break; ?>
              <?php } ?>
            <?php } ?>
          </ul>

          <?php if ($previewIsLimited) { ?>
            <div class="alert alert-warning">
              <?php echo __('Only %1% terms were shown.', ['%1%' => $previewSize]); ?>
            </div>
          <?php } ?>
        <?php } ?>
      </div>
    <?php } ?>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'term'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
