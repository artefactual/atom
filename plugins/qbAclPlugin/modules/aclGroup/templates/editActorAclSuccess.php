<?php use_helper('Javascript') ?>

<h1><?php echo __('Edit %1% permissions of %2%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_actor')), '%2%' => render_title($resource))) ?></h1>

<?php echo get_partial('addActorDialog', array('basicActions' => $basicActions)) ?>

<form method="post" action="<?php echo url_for(array($resource, 'module' => 'aclGroup', 'action' => 'editActorAcl')) ?>" id="editForm">

  <section id="content">

    <fieldset class="collapsible">

      <legend><?php echo __('Permissions for all %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_actor')))) ?></legend>

      <?php foreach ($actors as $objectId => $permissions): ?>
        <div class="form-item">
          <?php echo get_component('aclGroup', 'aclTable', array('object' => QubitActor::getById($objectId), 'permissions' => $permissions, 'actions' => $basicActions)) ?>
        </div>
      <?php endforeach; ?>

      <div class="form-item">
        <label for="addActorLink"><?php echo __('Add permissions by %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_actor')))) ?></label>
        <a id="addActorLink" href="javascript:myDialog.show()"><?php echo __('Add %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_actor')))) ?></a>
      </div>

    </fieldset>

  </section>

  <section class="actions">
    <ul>
      <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'aclGroup', 'action' => 'indexActorAcl'), array('class' => 'c-btn')) ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
    </ul>
  </section>

</form>
