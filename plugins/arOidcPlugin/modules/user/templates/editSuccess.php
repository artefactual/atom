<?php decorate_with('layout_1col.php'); ?>
<?php use_helper('Javascript'); ?>

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

    <section id="content">

      <?php if ($sf_user->user != $resource) { ?>
        <fieldset class="collapsible" id="basicInfo">

          <legend><?php echo __('Basic info'); ?></legend>

          <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
            <?php echo $form->username->renderRow(); ?>
            <?php echo $form->email->renderRow(); ?>
          <?php } ?>

          <?php echo $form->active
              ->label(__('Active'))
              ->renderRow(); ?>

        </fieldset> <!-- /#basicInfo -->
      <?php } ?>

      <fieldset class="collapsible" id="groupsAndPermissions">

        <legend><?php echo __('Access control'); ?></legend>

        <?php echo $form->groups
            ->label(__('User groups'))
            ->renderRow(['class' => 'form-autocomplete']); ?>

        <?php echo $form->translate
            ->label(__('Allowed languages for translation'))
            ->renderRow(['class' => 'form-autocomplete']); ?>

        <?php if ($restEnabled) { ?>
          <?php echo $form->restApiKey
            ->label(__('REST API access key'.((isset($restApiKey)) ? ': <code>'.$restApiKey.'</code>' : '')))
            ->renderRow(); ?>
        <?php } ?>

        <?php if ($oaiEnabled) { ?>
          <?php echo $form->oaiApiKey
            ->label(__('OAI-PMH API access key'.((isset($oaiApiKey)) ? ': <code>'.$oaiApiKey.'</code>' : '')))
            ->renderRow(); ?>
        <?php } ?>

      </fieldset> <!-- /#groupsAndPermissions -->

    </section>

    <section class="actions">
      <ul>
        <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
          <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'c-btn']); ?></li>
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
        <?php } else { ?>
          <li><?php echo link_to(__('Cancel'), ['module' => 'user', 'action' => 'list'], ['class' => 'c-btn']); ?></li>
          <?php if (false === sfContext::getinstance()->user->getProviderConfigValue('auto_create_atom_user', true)) { ?>
            <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
          <?php } ?>
        <?php } ?>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
