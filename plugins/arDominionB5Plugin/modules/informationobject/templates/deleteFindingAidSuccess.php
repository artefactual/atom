<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete the finding aid of %1%?', ['%1%' => $resource->title]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'deleteFindingAid']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>
    
    <div id="content" class="p-3">
      <?php echo __('The following file will be deleted from the file system:'); ?>

      <ul class="mb-0">
        <li><a href="<?php echo public_path($path); ?>" target="_blank"><?php echo $filename; ?></a></li>
        <li><?php echo __('If the finding aid is an uploaded PDF, the transcript will be deleted too.'); ?></li>
      </ul>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
