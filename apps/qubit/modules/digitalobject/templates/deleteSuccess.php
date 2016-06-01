<?php decorate_with('layout_1col.php') ?>

<?php slot('title') ?>
  <?php if (isset($resource->parent)): ?>
    <h1><?php echo __('Are you sure you want to delete this reference/thumbnail representation?') ?></h1>
  <?php else: ?>
    <h1><?php echo __('Are you sure you want to delete the %1% linked to %2%?', array('%1%' => mb_strtolower(sfConfig::get('app_ui_label_digitalobject')), '%2%' => render_title($informationObject))) ?></h1>
  <?php endif; ?>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'digitalobject', 'action' => 'delete')), array('method' => 'delete')) ?>

    <section class="actions">
      <ul>
        <?php if (isset($resource->parent)): ?>
          <li><?php echo link_to(__('Cancel'), array($resource->parent, 'module' => 'digitalobject', 'action' => 'edit'), array('class' => 'c-btn')) ?></li>
        <?php else: ?>
          <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'digitalobject', 'action' => 'edit'), array('class' => 'c-btn')) ?></li>
        <?php endif; ?>
        <li><input class="c-btn c-btn-delete" type="submit" value="<?php echo __('Delete') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
