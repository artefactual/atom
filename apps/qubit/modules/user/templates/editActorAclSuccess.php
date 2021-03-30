<?php use_helper('Javascript'); ?>

<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor')), '%2%' => render_title($resource)]); ?></h1>

<?php echo get_partial('aclGroup/addActorDialog', ['basicActions' => $basicActions]); ?>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'editActorAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>
  
  <section id="content">

    <fieldset class="collapsible">

      <legend><?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></legend>

      <?php foreach ($actors as $key => $item) { ?>
        <div class="form-item">
          <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitActor::getById($key), 'permissions' => $item, 'actions' => $basicActions]); ?>
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
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user', 'action' => 'indexActorAcl'], ['class' => 'c-btn']); ?></li>
      <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
    </ul>
  </section>

</form>
