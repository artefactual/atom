<?php decorate_with('layout_2col') ?>

<?php slot('sidebar') ?>
  <?php include_component('informationobject', 'contextMenu') ?>
<?php end_slot() ?>

<?php slot('title') ?>
  <h1><?php echo __('%1 - report criteria', array('%1' => $type)) ?></h1>
  <h2><?php echo render_title($resource) ?></h2>
<?php end_slot() ?>

<?php slot('before-content') ?>
  <?php echo $form->renderGlobalErrors() ?>
  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'informationobject', 'action' => 'boxLabel', 'type' => $type)), array('class' => 'form-inline')) ?>
<?php end_slot() ?>

<fieldset class="single">
  <div class="fieldset-wrapper">
    <?php echo render_field($form->format->label(__('Format')), $resource) ?>
  </div>
</fieldset>

<?php slot('after-content') ?>
  <section class="actions">
    <ul class="clearfix links">
      <li><input class="form-submit c-btn c-btn-submit" type="submit" value="<?php echo __('Continue') ?>"/></li>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'informationobject'), array('class' => 'c-btn')) ?></li>
    </ul>
  </section>
  </form>
<?php end_slot() ?>
