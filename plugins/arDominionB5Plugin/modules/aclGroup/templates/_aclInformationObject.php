<h1>
  <?php echo __('Edit %1% permissions of %2%', [
      '%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject')),
      '%2%' => render_title($resource),
  ]); ?>
</h1>

<?php echo get_partial('aclGroup/aclModal', [
    'entityType' => 'informationobject',
    'label' => sfConfig::get('app_ui_label_informationobject'),
    'basicActions' => $basicActions,
]); ?>

<?php echo get_partial('aclGroup/aclModal', [
    'entityType' => 'repository',
    'label' => sfConfig::get('app_ui_label_repository'),
    'basicActions' => $basicActions,
]); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(
    url_for([
        $resource,
        'module' => $sf_context->getModuleName(),
        'action' => 'editInformationObjectAcl',
    ]),
    ['id' => 'editForm']
); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion mb-3">
    <div class="accordion-item">
      <h2 class="accordion-header" id="all-heading">
        <button
          class="accordion-button"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#all-collapse"
          aria-expanded="true"
          aria-controls="all-collapse">
          <?php echo __(
              'Permissions for all %1%',
              ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]
          ); ?>
        </button>
      </h2>
      <div
        id="all-collapse"
        class="accordion-collapse collapse show"
        aria-labelledby="all-heading">
        <div class="accordion-body">
          <?php echo get_component('aclGroup', 'aclTable', [
              'object' => QubitInformationObject::getRoot(),
              'permissions' => $root,
              'actions' => $basicActions,
          ]); ?>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="io-heading">
        <button
          class="accordion-button collapsed"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#io-collapse"
          aria-expanded="false"
          aria-controls="io-collapse">
          <?php echo __(
              'Permissions by %1%',
              ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]
          ); ?>
        </button>
      </h2>
      <div id="io-collapse" class="accordion-collapse collapse" aria-labelledby="io-heading">
        <div class="accordion-body">
          <?php if (0 < count($informationObjects)) { ?>
            <?php foreach ($informationObjects as $informationObjectId => $permissions) { ?>
              <?php echo get_component('aclGroup', 'aclTable', [
                  'object' => QubitInformationObject::getById($informationObjectId),
                  'permissions' => $permissions,
                  'actions' => $basicActions,
              ]); ?>
            <?php } ?>
          <?php } ?>

          <button
            class="btn atom-btn-white text-wrap"
            type="button"
            id="acl-add-informationobject"
            data-bs-toggle="modal"
            data-bs-target="#acl-modal-container-informationobject">
            <i class="fas fa-plus me-1" aria-hidden="true"></i>
            <?php echo __(
                'Add permissions by %1%',
                ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]
            ); ?>
          </button>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="repo-heading">
        <button
          class="accordion-button collapsed"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#repo-collapse"
          aria-expanded="false"
          aria-controls="repo-collapse">
          <?php echo __(
              'Permissions by %1%',
              ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]
          ); ?>
        </button>
      </h2>
      <div
        id="repo-collapse"
        class="accordion-collapse collapse"
        aria-labelledby="repo-heading">
        <div class="accordion-body">
          <?php if (0 < count($repositories)) { ?>
            <?php foreach ($repositories as $repository => $permissions) { ?>
              <?php echo get_component('aclGroup', 'aclTable', [
                  'object' => QubitRepository::getBySlug($repository),
                  'permissions' => $permissions,
                  'actions' => $basicActions,
              ]); ?>
            <?php } ?>
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

  <ul class="actions mb-3 nav gap-2">
    <li>
      <?php echo link_to(
          __('Cancel'),
          [$resource, 'module' => $sf_context->getModuleName(), 'action' => 'indexInformationObjectAcl'],
          ['class' => 'btn atom-btn-outline-light', 'role' => 'button']
      ); ?>
    </li>
    <li>
      <input
        class="btn atom-btn-outline-success"
        type="submit"
        value="<?php echo __('Save'); ?>">
    </li>
  </ul>

</form>
