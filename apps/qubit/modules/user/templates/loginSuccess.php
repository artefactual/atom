<?php use_helper('Javascript') ?>

<div class="row">

  <div class="span4 offset4 well well-white">

    <?php if ('user' != $sf_request->module || 'login' != $sf_request->action): ?>
      <div class="messages status">
        <?php echo __('Please log in to access that page') ?>
      </div>
    <?php endif; ?>

    <legend><?php echo _('Sign in') ?></legend>

    <?php if ($form->hasErrors()): ?>
      <div class="alert alert-error">
        <a class="close" data-dismiss="alert" href="#">×</a>
        <?php echo $form->renderGlobalErrors() ?>
      </div>
    <?php endif; ?>

    <?php echo $form->renderFormTag(url_for(array('module' => 'user', 'action' => 'login'))) ?>

      <?php echo $form->renderHiddenFields() ?>

      <?php echo $form->email->renderRow(array('autofocus' => 'autofocus')) ?>

      <?php echo $form->password->renderRow() ?>

      <div class="control-group">
        <div class="controls">
          <button type="submit" class="btn"><?php echo _('Sign in') ?></button>
        </div>
      </div>

    </form>
  </div>
</div>
