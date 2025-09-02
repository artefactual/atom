<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php echo $form->renderGlobalErrors(); ?>
  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'physicalobject', 'action' => 'delete']), ['method' => 'delete']); ?>
    <?php echo $form->renderHiddenFields(); ?>

    <?php if (0 < count($informationObjects)) { ?>
      <div id="content" class="p-3">
        <?php echo __('Click Confirm to delete this physical storage from the system. This will also remove the physical storage location from the following records:'); ?>
        <ul class="mb-0">
          <?php foreach ($informationObjects as $item) { ?>
            <li><?php echo link_to(render_title($item), [$item, 'module' => 'informationobject']); ?></li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'physicalobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>
  </form>
<?php end_slot(); ?>
