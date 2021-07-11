<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject')), '%2%' => render_title($resource)]); ?></h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'editInformationObjectAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="all-heading">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#all-collapse" aria-expanded="true" aria-controls="all-collapse">
          <?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?>
        </button>
      </h2>
      <div id="all-collapse" class="accordion-collapse collapse show" aria-labelledby="all-heading">
        <div class="accordion-body">
          <div class="form-item">
            <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitInformationObject::getRoot(), 'permissions' => $root, 'actions' => $basicActions]); ?>
          </div>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="io-heading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#io-collapse" aria-expanded="false" aria-controls="io-collapse">
          <?php echo __('Permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?>
        </button>
      </h2>
      <div id="io-collapse" class="accordion-collapse collapse" aria-labelledby="io-heading">
        <div class="accordion-body">
          <?php if (0 < count($informationObjects)) { ?>
            <?php foreach ($informationObjects as $informationObjectId => $permissions) { ?>
              <div class="form-item">
                <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitInformationObject::getById($informationObjectId), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
              </div>
            <?php } ?>
          <?php } ?>

          <!-- Add info object div - cut by aclDialog.js
          <div class="form-item" id="addInformationObject">
            <label for="addInformationObject"><?php echo __('%1% name', ['%1%' => sfConfig::get('app_ui_label_informationobject')]); ?></label>
            <select name="addInformationObject" id="addInformationObject" class="form-autocomplete"></select>
            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete']); ?>"/>
          </div>
          -->

          <div class="form-item">
            <label for="addInformationObjectLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></label>
            <a id="addInformationObjectLink" href="#"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></a>
          </div>
        </div>
      </div>
    </div>
    <div class="accordion-item">
      <h2 class="accordion-header" id="repo-heading">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#repo-collapse" aria-expanded="false" aria-controls="repo-collapse">
          <?php echo __('Permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?>
        </button>
      </h2>
      <div id="repo-collapse" class="accordion-collapse collapse" aria-labelledby="repo-heading">
        <div class="accordion-body">
          <?php if (0 < count($repositories)) { ?>
            <?php foreach ($repositories as $repository => $permissions) { ?>
              <div class="form-item">
                <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitRepository::getBySlug($repository), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
              </div>
            <?php } ?>
          <?php } ?>

          <!-- Add repository div - cut by aclDialog.js
          <div class="form-item" id="addRepository">
            <label for="addRepository"><?php echo __('%1% name', ['%1%' => sfConfig::get('app_ui_label_repository')]); ?></label>
            <select name="addRepository" id="addRepository" class="form-autocomplete"></select>
            <input class="list" type="hidden" value="<?php echo url_for(['module' => 'repository', 'action' => 'autocomplete']); ?>"/>
          </div>
          -->

          <div class="form-item">
            <label for="addRepositoryLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></label>
            <a id="addRepositoryLink" href="#"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ul class="actions nav gap-2">
    <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user', 'action' => 'indexInformationObjectAcl'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
  </ul>

</form>
