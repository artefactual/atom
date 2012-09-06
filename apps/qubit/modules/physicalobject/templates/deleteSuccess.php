<h1><?php echo __('Are you sure you want to delete %1%?', array('%1%' => render_title($resource))) ?></h1>

<?php if (0 < count($informationObjects)): ?>
  <h2><?php echo __('Click Confirm to delete this physical storage from the system. This will also remove the physical storage location from the following records:') ?></h2>
  <ul>
    <?php foreach ($informationObjects as $item): ?>
      <li><?php echo link_to(render_title($item), array($item, 'module' => 'informationobject')) ?></li>
    <?php endforeach; ?>
  </ul>
<?php endif; ?>

<?php echo $form->renderGlobalErrors() ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'physicalobject', 'action' => 'delete')), array('method' => 'delete')) ?>

  <?php echo $form->renderHiddenFields() ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'physicalobject')) ?></li>
        <li><input class="form-submit danger" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
