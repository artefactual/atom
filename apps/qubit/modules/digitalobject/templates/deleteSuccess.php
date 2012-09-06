<?php if (isset($resource->parent)): ?>
  <h1><?php echo __('Are you sure you want to delete this reference/thumbnail representation?') ?></h1>
<?php else: ?>
  <h1><?php echo __('Are you sure you want to delete the digital object linked to %1%?', array('%1%' => render_title($informationObject))) ?></h1>
<?php endif; ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'digitalobject', 'action' => 'delete')), array('method' => 'delete')) ?>

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">

        <?php if (isset($resource->parent)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource->parent, 'module' => 'digitalobject', 'action' => 'edit')) ?></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'digitalobject', 'action' => 'edit')) ?></li>
        <?php endif; ?>

        <li><input class="form-submit danger" type="submit" value="<?php echo __('Delete') ?>"/></li>

      </ul>
    </div>

  </div>

</form>
