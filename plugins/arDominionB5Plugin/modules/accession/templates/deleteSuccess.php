<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'accession', 'action' => 'delete']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <?php if (0 < count($resource->deaccessions)) { ?>
        <h2><?php echo __('It has %1% deaccessions that will also be deleted:', ['%1%' => count($resource->deaccessions)]); ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($resource->deaccessions as $item) { ?>
              <li><?php echo link_to(render_title($item), [$item, 'module' => 'deaccession']); ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

      <?php if (0 < count($accruals)) { ?>
        <h2><?php echo __('It has %1% accruals. They will not be deleted.', ['%1%' => count($accruals)]); ?></h2>
        <div class="delete-list">
          <ul>
            <?php foreach ($accruals as $item) { ?>
              <li><?php echo link_to(render_title($item), [$item, 'module' => 'accession']); ?></li>
            <?php } ?>
          </ul>
        </div>
      <?php } ?>

    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'accession'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
