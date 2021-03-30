<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'term', 'action' => 'delete']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <?php if (0 < $resource->getRelatedObjectCount()) { ?>
        <h2><?php echo __('This term is used in %1% records. The term will be deleted from these records.', ['%1%' => $resource->getRelatedObjectCount()]); ?></h2>
        <div class="text-section">
          <?php echo __('The related object(s) will <strong>not</strong> be deleted'); ?>
        </div>
      <?php } ?>

      <?php if (0 < count($resource->events)) { ?>
        <h2><?php echo __('It\'s used in %1% events that will also be deleted', ['%1%' => count($resource->events)]); ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($resource->events as $item) { ?>
              <li><?php echo render_value_inline(Qubit::renderDateStartEnd($item->getDate(['cultureFallback' => true]), $item->startDate, $item->endDate)); ?> (<?php echo render_title($resource); ?>) <?php echo link_to(render_title($item->object), [$item->object, 'module' => 'informationobject']); ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <?php if (0 < $count) { ?>
        <h2><?php echo __('It has %1% descendants that will also be deleted', ['%1%' => $count]); ?></h2>
        <div class="delete-list">

          <ul>
            <?php foreach ($resource->descendants as $index => $item) { ?>
              <li><?php echo link_to(render_title($item), [$item, 'module' => 'term']); ?></li>
              <?php if ($index + 1 == $previewSize) { ?>
                <?php break; ?>
              <?php } ?>
            <?php } ?>
          </ul>

          <?php if ($previewIsLimited) { ?>
            <hr />
            <p>
              <?php echo __('Only %1% terms were shown.', ['%1%' => $previewSize]); ?>
            </p>
          <?php } ?>

        </div>
      <?php } ?>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'term'], ['class' => 'c-btn']); ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
