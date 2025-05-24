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

        <?php echo $form->renderGlobalErrors(); ?>

        <?php echo $form->renderFormTag(url_for(['module' => 'oidc', 'action' => 'login'])); ?>

          <?php echo $form->renderHiddenFields(); ?>

          <button type="submit"><?php echo __('Log in with SSO'); ?></button>

        </form>

      </div>

    </div>

  </div>

<?php end_slot(); ?>
