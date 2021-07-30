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
    
    <?php $settings = json_encode(['password' => [
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
        'username' => '',
    ]]); ?>

    <div class="accordion">
      <div class="accordion-item">
        <h2 class="accordion-header" id="password-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#password-collapse" aria-expanded="true" aria-controls="password-collapse">
            <?php echo __('Edit your password'); ?>
          </button>
        </h2>
        <div id="password-collapse" class="accordion-collapse collapse show" aria-labelledby="password-heading">
          <div class="accordion-body">
            <?php echo render_field($form->password->label(__('New password'))); ?>
            <?php echo render_field($form->confirmPassword->label(__('Confirm password'))); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
