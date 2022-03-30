<?php decorate_with('layout_1col.php'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for([$resource, 'module' => 'user', 'action' => 'passwordEdit']), ['id' => 'editForm']); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="password-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#password-collapse" aria-expanded="true" aria-controls="password-collapse">
            <?php echo __('Reset password'); ?>
          </button>
        </h2>
        <div id="password-collapse" class="accordion-collapse collapse show" aria-labelledby="password-heading">
          <div class="accordion-body">
            <div class="row">
              <div class="col-md-6">
                <div
                    hidden
                    class="password-strength-settings"
                    data-not-strong="<?php echo __('Your password is not strong enough.'); ?>"
                    data-strength-title="<?php echo __('Password strength:'); ?>"
                    data-require-strong-password="<?php echo sfConfig::get('app_require_strong_passwords', false); ?>"
                    data-too-short="<?php echo __('Make it at least six characters'); ?>"
                    data-add-lower-case="<?php echo __('Add lowercase letters'); ?>"
                    data-add-upper-case="<?php echo __('Add uppercase letters'); ?>"
                    data-add-numbers="<?php echo __('Add numbers'); ?>"
                    data-add-punctuation="<?php echo __('Add punctuation'); ?>"
                    data-username="<?php echo $resource->username; ?>"
                    data-same-as-username="<?php echo __('Make it different from your username'); ?>"
                    data-confirm-failure="<?php echo __('Your password confirmation did not match your password.'); ?>"
                  >
                </div>
                <?php echo render_field($form->password->label(__('New password')), null, ['class' => 'password-strength', 'required' => 'required']); ?>
                <?php echo render_field($form->confirmPassword->label(__('Confirm password')), null, ['class' => 'password-confirm', 'required' => 'required']); ?>
              </div>
              <div class="col-md-6 template" hidden>
                <div class="mb-3 bg-light p-3 rounded border-start border-4">
                  <label class="form-label"><?php echo __('Password strength:'); ?></label>
                  <div class="progress mb-3">
                    <div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <li><?php echo link_to(__('Cancel'), [$resource, 'module' => 'user'], ['class' => 'btn atom-btn-outline-light', 'role' => 'button']); ?></li>
      <li><input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>"></li>
    </ul>

  </form>

<?php end_slot(); ?>
