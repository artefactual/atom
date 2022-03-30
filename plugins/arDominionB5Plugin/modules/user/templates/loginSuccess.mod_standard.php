<?php decorate_with('layout_1col'); ?>
<?php use_helper('Javascript'); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(['module' => 'user', 'action' => 'login'])); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div class="accordion mb-3">
      <div class="accordion-item">
        <h2 class="accordion-header" id="login-heading">
          <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#login-collapse" aria-expanded="true" aria-controls="login-collapse">
            <?php if ('user' != $sf_request->module || 'login' != $sf_request->action) { ?>
              <?php echo __('Please log in to access that page'); ?>
            <?php } else { ?>
              <?php echo __('Log in'); ?>
            <?php } ?>
          </button>
        </h2>
        <div id="login-collapse" class="accordion-collapse collapse show" aria-labelledby="login-heading">
          <div class="accordion-body">
            <?php echo render_field($form->email, null, ['type' => 'email', 'autofocus' => 'autofocus', 'required' => 'required']); ?>

            <?php echo render_field($form->password, null, ['type' => 'password', 'autocomplete' => 'off', 'required' => 'required']); ?>
          </div>
        </div>
      </div>
    </div>

    <ul class="actions mb-3 nav gap-2">
      <button type="submit" class="btn atom-btn-outline-success"><?php echo __('Log in'); ?></button>
    </ul>

  </form>

<?php end_slot(); ?>
