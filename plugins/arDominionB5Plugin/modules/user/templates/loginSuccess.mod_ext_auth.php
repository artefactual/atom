<?php decorate_with('layout_1col'); ?>
<?php use_helper('Javascript'); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

    <?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
      <?php echo $form->renderFormTag(url_for(['module' => 'cas', 'action' => 'login'])); ?>
    <?php } elseif ($sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
      <?php echo $form->renderFormTag(url_for(['module' => 'oidc', 'action' => 'login'])); ?>
    <?php } ?>

    <?php echo $form->renderHiddenFields(); ?>

    <ul class="actions mb-3 nav gap-2">
      <button type="submit" class="btn atom-btn-outline-success">
      <?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
        <?php echo __('Log in with CAS'); ?>
      <?php } elseif ($sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
        <?php echo __('Log in with SSO'); ?>
      <?php } ?>
      </button>
    </ul>

  </form>

<?php end_slot(); ?>
