<?php decorate_with('layout_2col'); ?>

<?php slot('sidebar'); ?>
  <?php include_component('informationobject', 'contextMenu'); ?>
<?php end_slot(); ?>

<?php slot('title'); ?>
  <h1><?php echo __('%1 - report criteria', ['%1' => $type]); ?></h1>
  <h2><?php echo render_title($resource); ?></h2>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <?php echo $form->renderGlobalErrors(); ?>
  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'informationobject', 'action' => 'boxLabel', 'type' => $type]), ['class' => 'form-inline']); ?>
  <?php echo $form->renderHiddenFields(); ?>
<?php end_slot(); ?>

<fieldset class="single">
  <div class="fieldset-wrapper">
    <?php echo render_field($form->format->label(__('Format')), $resource); ?>
  </div>
</fieldset>

<?php slot('after-content'); ?>
  <ul class="actions nav gap-2">
    <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Continue'); ?>"></li>
    <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'informationobject'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
  </ul>

  </form>
<?php end_slot(); ?>
