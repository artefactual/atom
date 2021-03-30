<?php use_helper('Javascript'); ?>

<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor')), '%2%' => render_title($resource)]); ?></h1>

<?php echo get_partial('addActorDialog', ['basicActions' => $basicActions]); ?>

<?php echo $form->renderGlobalErrors(); ?>

<form method="post" action="<?php echo url_for([$resource, 'module' => 'aclGroup', 'action' => 'editActorAcl']); ?>" id="editForm">

  <?php echo $form->renderHiddenFields(); ?>

  <section id="content">

    <fieldset class="collapsible">

      <legend><?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></legend>

      <?php foreach ($actors as $objectId => $permissions) { ?>
        <div class="form-item">
          <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitActor::getById($objectId), 'permissions' => $permissions, 'actions' => $basicActions]); ?>
        </div>
      <?php } ?>

      <div class="form-item">
        <label for="addActorLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></label>
        <a id="addActorLink" href="javascript:myDialog.show()"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></a>
      </div>

    </fieldset>

  </section>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'aclGroup', 'action' => 'indexActorAcl'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
    </ul>
  </section>

</form>
