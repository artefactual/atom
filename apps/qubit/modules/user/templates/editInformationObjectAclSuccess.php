<?php use_helper('Javascript'); ?>

<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject')), '%2%' => render_title($resource)]); ?></h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'editInformationObjectAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>
  
  <section id="content">

    <fieldset class="collapsible" id="allInfoObjectsArea">

      <legend><?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></legend>

      <div class="form-item">
        <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitInformationObject::getRoot(), 'permissions' => $root, 'actions' => $basicActions]); ?>
      </div>

    </fieldset> <!-- /#allInfoObjectsArea -->

    <fieldset class="collapsible collapsed" id="informationObjectArea">

      <legend><?php echo __('Permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></legend>

      <?php if (0 < count($informationObjects)) { ?>
        <?php foreach ($informationObjects as $informationObjectId => $permissions) { ?>
          <div class="form-item">
            <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitInformationObject::getById($informationObjectId), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
          </div>
        <?php } ?>
      <?php } ?>

      <?php

        // Build dialog for adding new table
        $tableTemplate = '<div class="form-item">';
        $tableTemplate .= '<table id="acl_{objectId}" class="table table-bordered">';
        $tableTemplate .= '<caption/>';
        $tableTemplate .= '<thead>';
        $tableTemplate .= '<tr>';
        $tableTemplate .= '<th scope="col">'.__('Action').'</th>';
        $tableTemplate .= '<th scope="col">'.__('Permissions').'</th>';
        $tableTemplate .= '</tr>';
        $tableTemplate .= '</thead>';
        $tableTemplate .= '<tbody>';

        foreach ($basicActions as $key => $value) {
            $tableTemplate .= '<tr>';
            $tableTemplate .= '<td>'.__($value).'</th>';
            $tableTemplate .= '<td><ul class="radio inline">';
            $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::GRANT.'"/>'.__('Grant').'</li>';
            $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::DENY.'"/>'.__('Deny').'</li>';
            $tableTemplate .= '<li><input type="radio" name="acl['.$key.'_{objectId}]" value="'.QubitAcl::INHERIT.'" checked/>'.__('Inherit').'</li>';
            $tableTemplate .= '</ul></td>';
            $tableTemplate .= '</tr>';
            $tableTemplate .= '</div>';
        }

        $tableTemplate .= '</tbody>';
        $tableTemplate .= '</table>';

        echo javascript_tag(<<<EOL
Drupal.behaviors.dialog = {
  attach: function (context)
  {
    Qubit.infoObjectDialog = new QubitAclDialog('addInformationObject', '{$tableTemplate}', jQuery);
  }
}
EOL
); ?>

      <!-- Add info object div - cut by aclDialog.js -->
      <div class="form-item" id="addInformationObject">
        <label for="addInformationObject"><?php echo __('%1% name', ['%1%' => sfConfig::get('app_ui_label_informationobject')]); ?></label>
        <select name="addInformationObject" id="addInformationObject" class="form-autocomplete"></select>
        <input class="list" type="hidden" value="<?php echo url_for(['module' => 'informationobject', 'action' => 'autocomplete']); ?>"/>
      </div>

      <div class="form-item">
        <label for="addInformationObjectLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></label>
        <a id="addInformationObjectLink" href="javascript:Qubit.infoObjectDialog.show()"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject'))]); ?></a>
      </div>

    </fieldset> <!-- /#informationObjectArea -->

    <fieldset class="collapsible collapsed" id="repositoryArea">

      <legend><?php echo __('Permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></legend>

      <?php if (0 < count($repositories)) { ?>
        <?php foreach ($repositories as $repository => $permissions) { ?>
          <div class="form-item">
            <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitRepository::getBySlug($repository), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
          </div>
        <?php } ?>
      <?php } ?>

      <?php echo javascript_tag(<<<EOL
Drupal.behaviors.dialog2 = {
  attach: function (context)
  {
    Qubit.repoDialog = new QubitAclDialog('addRepository', '{$tableTemplate}', jQuery);
  }
}
EOL
); ?>

      <!-- Add repository div - cut by aclDialog.js -->
      <div class="form-item" id="addRepository">
        <label for="addRepository"><?php echo __('%1% name', ['%1%' => sfConfig::get('app_ui_label_repository')]); ?></label>
        <select name="addRepository" id="addRepository" class="form-autocomplete"></select>
        <input class="list" type="hidden" value="<?php echo url_for(['module' => 'repository', 'action' => 'autocomplete']); ?>"/>
      </div>

      <div class="form-item">
        <label for="addRepositoryLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></label>
        <a id="addRepositoryLink" href="javascript:Qubit.repoDialog.show()"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></a>
      </div>

    </fieldset> <!-- /#repositoryArea -->

  </section>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user', 'action' => 'indexInformationObjectAcl'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
    </ul>
  </section>

</form>
