<h1><?php echo __('Edit %1% permissions of %2%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor')), '%2%' => render_title($resource)]); ?></h1>

<?php echo $form->renderGlobalErrors(); ?>

<?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'editActorAcl']), ['id' => 'editForm']); ?>

  <?php echo $form->renderHiddenFields(); ?>

  <div class="accordion">
    <div class="accordion-item">
      <h2 class="accordion-header" id="permissions-heading">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#permissions-collapse" aria-expanded="true" aria-controls="permissions-collapse">
          <?php echo __('Permissions for all %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?>
        </button>
      </h2>
      <div id="permissions-collapse" class="accordion-collapse collapse show" aria-labelledby="permissions-heading">
        <div class="accordion-body">
          <?php foreach ($actors as $key => $item) { ?>
            <div class="form-item">
              <?php echo get_component('aclGroup', 'aclTable', ['object' => QubitActor::getById($key), 'permissions' => $item, 'actions' => $basicActions]); ?>
            </div>
          <?php } ?>

          <div class="form-item">
            <label for="addActorLink"><?php echo __('Add permissions by %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></label>
            <a id="addActorLink" href="#"><?php echo __('Add %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_actor'))]); ?></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <ul class="actions nav gap-2">
    <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user', 'action' => 'indexActorAcl'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
    <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
  </ul>

</form>
