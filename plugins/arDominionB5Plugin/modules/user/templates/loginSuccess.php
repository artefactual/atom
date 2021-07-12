<?php decorate_with('layout_1col'); ?>
<?php use_helper('Javascript'); ?>

<?php slot('content'); ?>

  <div class="row">

    <div class="offset4 span4">

      <div id="content">

        <?php if ('user' != $sf_request->module || 'login' != $sf_request->action) { ?>
          <h1><?php echo __('Please log in to access that page'); ?></h1>
        <?php } else { ?>
          <h1><?php echo __('Log in'); ?></h1>
        <?php } ?>

        <?php if ($form->hasErrors()) { ?>
          <?php echo $form->renderGlobalErrors(); ?>
        <?php } ?>

        <?php echo $form->renderFormTag(url_for(['module' => 'user', 'action' => 'login'])); ?>

          <?php echo $form->renderHiddenFields(); ?>

          <?php echo render_field($form->email, null, ['autofocus' => 'autofocus']); ?>

          <?php echo render_field($form->password, null, ['autocomplete' => 'off']); ?>

          <button type="submit" class="btn atom-btn-secondary"><?php echo __('Log in'); ?></button>

        </form>

      </div>

    </div>

  </div>

<?php end_slot(); ?>
