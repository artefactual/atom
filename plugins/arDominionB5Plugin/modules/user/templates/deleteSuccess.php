<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource->username)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <?php if ($noteCount = $resource->getNotes()->count()) { ?>
    <div id="content" class="p-3">
      <?php echo __('This user has %1% note(s) in the system. These notes will not be deleted, but their association with this user will be removed.',
                    ['%1%' => $noteCount]); ?>
    </div>
  <?php } ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'delete']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-danger" type="submit" value="<?php echo __('Delete'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
