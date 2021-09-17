<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'delete']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <?php if (0 < $count) { ?>
      <div id="content" class="p-3">
        <?php echo __('It has %1% descendants that will also be deleted:', ['%1%' => $count]); ?>
        <ul class="mb-0">
          <?php foreach ($resource->descendants as $index => $item) { ?>
            <li><?php echo link_to(render_title($item), [$item, 'module' => 'informationobject']); ?></li>
            <?php if ($index + 1 == $previewSize) { ?>
              <?php break; ?>
            <?php } ?>
          <?php } ?>
        </ul>

        <?php if ($previewIsLimited) { ?>
          <div class="alert alert-warning mt-3 mb-0">
            <?php echo __('Only %1% descriptions were shown.', ['%1%' => $previewSize]); ?>
            <?php echo link_to(
                __('View the full list of descendants.'),
                ['module' => 'informationobject', 'action' => 'browse', 'collection' => $resource->id, 'topLod' => false],
                ['class' => 'alert-link']
            ); ?>
          </div>
        <?php } ?>
      </div>
    <?php } ?>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
