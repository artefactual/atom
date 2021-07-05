<h1 class="multiline">
  <?php echo __('Edit %1% permissions', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?>
  <span class="sub"><?php echo render_title($resource); ?></span>
</h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'aclGroup', 'action' => 'editRepositoryAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="permissions-heading">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#permissions-collapse" aria-expanded="true" aria-controls="permissions-collapse">
          <?php echo __('Edit permissions'); ?>
        </button>
      </h2>
      <div id="permissions-collapse" class="accordion-collapse collapse show" aria-labelledby="permissions-heading">
        <div class="accordion-body">
          <?php foreach ($repositories as $objectId => $permissions) { ?>
            <div class="form-item">
              <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitRepository::getById($objectId), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
            </div>
          <?php } ?>

          <div class="form-item">
            <label for="addRepositoryLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></label>
            <a id="addRepositoryLink" href="javascript:myDialog.show()"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'aclGroup', 'action' => 'indexRepositoryAcl'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
    </ul>
  </section>

</form>
