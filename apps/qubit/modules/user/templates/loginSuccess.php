<?php decorate_with('layout_1col') ?>
<?php use_helper('Javascript') ?>

<?php slot('content') ?>

  <div class="row">

    <div class="offset4 span4">

      <div id="content">

        <?php if ('user' != $sf_request->module || 'login' != $sf_request->action): ?>
          <h1><?php echo __('Please log in to access that page') ?></h1>
        <?php else: ?>
          <h1><?php echo __('Log in') ?></h1>
        <?php endif; ?>

        <?php if ($form->hasErrors()): ?>
          <?php echo $form->renderGlobalErrors() ?>
        <?php endif; ?>

        <?php echo $form->renderFormTag(url_for(array('module' => 'user', 'action' => 'login'))) ?>

          <?php echo $form->renderHiddenFields() ?>

          <?php echo $form->email->renderRow(array('autofocus' => 'autofocus', 'class' => 'input-block-level')) ?>

          <?php echo $form->password->renderRow(array('class' => 'input-block-level', 'autocomplete' => 'off')) ?>

          <section class="actions">
            <button type="submit" class="btn btn-primary btn-block btn-large"><?php echo __('Log in') ?></button>
          </section>

        </form>

      </div>

    </div>

  </div>

<?php end_slot() ?>
