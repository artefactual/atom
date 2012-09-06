<?php use_helper('Javascript') ?>

<h1><?php echo __('Reset password'); ?></h1>

<h1 class="label"><?php echo render_title($resource) ?></h1>

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

  <div class="actions section">

    <h2 class="element-invisible"><?php echo __('Actions') ?></h2>

    <div class="content">
      <ul class="clearfix links">
        <li><?php echo link_to(__('Cancel'), array($resource, 'module' => 'user')) ?></li>
        <li><input class="form-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </div>

  </div>

</form>
