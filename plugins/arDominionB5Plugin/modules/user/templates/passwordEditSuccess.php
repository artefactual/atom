<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1 class="multiline">
    <?php echo __('Reset password'); ?>
    <span class="sub"><?php echo render_title($resource); ?>
  </h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'passwordEdit']), ['id' => 'editForm']); ?>

    <?php echo $form->renderHiddenFields(); ?>
    
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

    <div class="accordion" id="user-password">
      <div class="accordion-item">
        <h2 class="accordion-header" id="password-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#password-collapse" aria-expanded="true" aria-controls="password-collapse">
            <?php echo __('Edit your password'); ?>
          </button>
        </h2>
        <div id="password-collapse" class="accordion-collapse collapse show" aria-labelledby="password-heading" data-bs-parent="#user-password">
          <div class="accordion-body">
            <?php echo $form->password->renderError(); ?>

            <div class="form-item password-parent">
              <?php echo $form->password
                  ->label(__('New password'))
                  ->renderLabel(); ?>
              <?php echo $form->password->render(['class' => 'password-field']); ?>
            </div>

            <div class="form-item confirm-parent">
              <?php echo $form->confirmPassword
                  ->label(__('Confirm password'))
                  ->renderLabel(); ?>
              <?php echo $form->confirmPassword->render(['class' => 'password-confirm']); ?>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section class="actions">
      <ul>
        <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'c-btn']); ?></li>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
