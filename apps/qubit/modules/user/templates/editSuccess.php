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

      <fieldset class="collapsible" id="basicInfo">

        <legend><?php echo __('Basic info'); ?></legend>

        <?php echo $form->username->renderRow(); ?>

        <?php echo $form->email->renderRow(); ?>

        <?php $settings = json_encode([
            'password' => [
                'strengthTitle' => __('Password strength:'),
                'hasWeaknesses' => __('To make your password stronger:'),
                'tooShort' => __('Make it at least six characters'),
                'addLowerCase' => __('Add lowercase letters'),
                'addUpperCase' => __('Add uppercase letters'),
                'addNumbers' => __('Add numbers'),
                'addPunctuation' => __('Add punctuation'),
                'sameAsUsername' => __('Make it different from your username'),
                'confirmSuccess' => __('Yes'),
                'confirmFailure' => __('No'),
                'confirmTitle' => __('Passwords match:'),
                'username' => '', ], ]); ?>

        <?php echo javascript_tag(<<<EOF
jQuery.extend(Drupal.settings, {$settings});
EOF
); ?>

        <?php echo $form->password->renderError(); ?>

        <div class="form-item password-parent">

          <?php if (isset($sf_request->getAttribute('sf_route')->resource)) { ?>
            <?php echo $form->password
              ->label(__('Change password'))
              ->renderLabel(); ?>
          <?php } else { ?>
            <?php echo $form->password
              ->label(__('Password'))
              ->renderLabel(); ?>
          <?php } ?>

          <?php echo $form->password->render(['class' => 'password-field']); ?>

        </div>

        <div class="form-item confirm-parent">
          <?php echo $form->password
              ->label(__('Confirm password'))
              ->renderLabel(); ?>
          <?php echo $form->confirmPassword->render(['class' => 'password-confirm']); ?>
        </div>

        <?php if ($sf_user->user != $resource) { ?>
          <?php echo $form->active
            ->label(__('Active'))
            ->renderRow(); ?>
        <?php } ?>

      </fieldset> <!-- /#basicInfo -->

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
          <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Create'); ?>"/></li>
        <?php } ?>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
