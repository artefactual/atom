<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('Are you sure you want to delete %1%?', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'rightsholder', 'action' => 'delete']), ['method' => 'delete']); ?>

    <?php echo $form->renderHiddenFields(); ?>
    
    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'rightsholder'], ['class' => 'c-btn']); ?></li>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
