<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository')), '%2%' => render_title($resource)]); ?></h1>

<?php echo get_partial('aclGroup/aclModal', [
    'entityType' => 'repository',
    'label' => sfConfig::get('app_ui_label_repository'),
    'basicActions' => $basicActions,
]); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'editRepositoryAcl']), ['id' => 'editForm']); ?>

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
          <?php foreach ($repositories as $key => $item) { ?>
            <?php echo get_component('aclGroup', 'aclTable', [
                'object' => QubitRepository::getById($key),
                'permissions' => $item,
                'actions' => $basicActions,
            ]); ?>
          <?php } ?>

          <button
            class="btn atom-btn-white text-wrap"
            type="button"
            id="acl-add-repository"
            data-bs-toggle="modal"
            data-bs-target="#acl-modal-container-repository">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __(
                'Add permissions by %1%',
                ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]
            ); ?>
          </button>
        </div>
      </div>
    </div>
  </div>

  <ul class="actions nav gap-2">
    <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user', 'action' => 'indexRepositoryAcl'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
  </ul>

</form>
