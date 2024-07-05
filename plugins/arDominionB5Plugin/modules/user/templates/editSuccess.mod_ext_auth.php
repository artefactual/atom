<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
    <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'edit']), ['id' => 'editForm']); ?>
  <?php } else { ?>
    <?php echo $form->renderFormTag(url_for(['module' => 'user', 'action' => 'add']), ['id' => 'editForm']); ?>
  <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <?php if ($sf_user->user != $resource) { ?>
        <div class="accordion-item">
          <h2 class="accordion-header" id="basic-heading">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#basic-collapse" aria-expanded="true" aria-controls="basic-collapse">
              <?php echo __('Basic info'); ?>
            </button>
          </h2>
          <div id="basic-collapse" class="accordion-collapse collapse show" aria-labelledby="basic-heading">
            <div class="accordion-body">

              <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
                <?php echo render_field($form->username); ?>
                <?php echo render_field($form->email, null, ['type' => 'email']); ?>
              <?php } ?>

              <?php echo render_field($form->active->label(__('Active'))); ?>
            </div>
          </div>
        </div>
      <?php } ?>
      <div class="accordion-item">
        <h2 class="accordion-header" id="access-heading">
          <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#access-collapse" aria-expanded="false" aria-controls="access-collapse">
            <?php echo __('Access control'); ?>
          </button>
        </h2>
        <div id="access-collapse" class="accordion-collapse collapse" aria-labelledby="access-heading">
          <div class="accordion-body">
            <?php echo render_field(
                $form->groups->label(__('User groups')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php echo render_field(
                $form->translate->label(__('Allowed languages for translation')),
                null,
                ['class' => 'form-autocomplete']
            ); ?>

            <?php if ($restEnabled) { ?>
              <?php echo render_field($form->restApiKey->label(
                  __('REST API access key'.((isset($restApiKey)) ? ': <code class="ms-2">'.$restApiKey.'</code>' : ''))
              )); ?>
            <?php } ?>

            <?php if ($oaiEnabled) { ?>
              <?php echo render_field($form->oaiApiKey->label(
                  __('OAI-PMH API access key'.((isset($oaiApiKey)) ? ': <code class="ms-2">'.$oaiApiKey.'</code>' : ''))
              )); ?>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
      <?php } else { ?>
        <li><?php echo link_to(__('Cancel'), ['module' => 'user', 'action' => 'list'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
        <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
          <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Create'); ?>"></li>
        <?php } ?>
      <?php } ?>
    </ul>

  </form>

<?php end_slot(); ?>
