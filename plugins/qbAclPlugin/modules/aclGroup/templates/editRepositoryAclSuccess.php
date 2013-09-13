<?php use_helper('Javascript') ?>

<h1 class="multiline">
  <?php echo __('Edit %1% permissions', array('%1%' => lcfirst(sfConfig::get('app_ui_label_repository')))) ?>
  <span class="sub"><?php echo render_title($resource) ?></span>
</h1>

<?php echo get_partial('addRepositoryDialog', array('basicActions' => $basicActions)) ?>

<?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'aclGroup', 'action' => 'editRepositoryAcl')), array('id' => 'editForm')) ?>

  <div id="content">

    <fieldset class="collapsible">

      <legend><?php echo __('Edit permissions') ?></legend>

      <?php foreach ($repositories as $objectId => $permissions): ?>
        <div class="form-item">
          <?php echo get_component('aclGroup', 'aclTable', array('object' => QubitRepository::getById($objectId), 'permissions' => $permissions, 'actions' => $basicActions)) ?>
        </div>
      <?php endforeach; ?>

      <div class="form-item">
        <label for="addRepositoryLink"><?php echo __('Add permissions by %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_repository')))) ?></label>
        <a id="addRepositoryLink" href="javascript:myDialog.show()"><?php echo __('Add %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_repository')))) ?></a>
      </div>

    </fieldset>

  </div>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'aclGroup', 'action' => 'indexRepositoryAcl'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
    </ul>
  </section>

</form>
