<?php decorate_with('layout_1col.php') ?>
<?php use_helper('Javascript') ?>

<?php slot('title') ?>
  <h1 class="multiline">
    <?php echo __('Reset password') ?>
    <span class="sub"><?php echo render_title($resource) ?>
  </h1>
<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array($resource, 'module' => 'user', 'action' => 'passwordEdit')), array('id' => 'editForm')) ?>

    <?php $settings = json_encode(array(
      'password' => array(
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
        'username' => ''))) ?>
    <?php echo javascript_tag(<<<EOF
jQuery.extend(Drupal.settings, $settings);
EOF
) ?>

    <section id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Edit your password') ?></legend>

        <?php echo $form->password->renderError() ?>

        <div class="form-item password-parent">
          <?php echo $form->password
            ->label(__('New password'))
            ->renderLabel() ?>
          <?php echo $form->password->render(array('class' => 'password-field')) ?>
        </div>

        <div class="form-item confirm-parent">
          <?php echo $form->confirmPassword
            ->label(__('Confirm password'))
            ->renderLabel() ?>
          <?php echo $form->confirmPassword->render(array('class' => 'password-confirm')) ?>
        </div>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'user'), array('class' => 'c-btn')) ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
