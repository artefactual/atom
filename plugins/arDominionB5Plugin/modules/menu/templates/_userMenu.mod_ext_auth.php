<?php if ($showLogin) { ?>
  <div class="dropdown my-2">
    <button class="btn btn-sm atom-btn-secondary dropdown-toggle" type="button" id="user-menu" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
      <?php echo $menuLabels['login']; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-lg-end mt-2" aria-labelledby="user-menu">
      <div>
        <h6 class="dropdown-header">
          <?php echo __('Have an account?'); ?>
        </h6>
      </div>
      <?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
        <?php echo $form->renderFormTag(url_for(['module' => 'cas', 'action' => 'login']), ['class' => 'mx-3 my-2']); ?>
      <?php } elseif ($sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
        <?php echo $form->renderFormTag(url_for(['module' => 'oidc', 'action' => 'login']), ['class' => 'mx-3 my-2']); ?>
      <?php } ?>
        <?php echo $form->renderHiddenFields(); ?>
        <button class="btn btn-sm atom-btn-secondary" type="submit">
          <?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
            <?php echo __('Log in with CAS'); ?>
          <?php } elseif ($sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
            <?php echo __('Log in with SSO'); ?>
          <?php } ?>
        </button>
      </form>
    </div>
  </div>
<?php } elseif ($sf_user->isAuthenticated()) { ?>
  <div class="dropdown my-2">
    <button class="btn btn-sm atom-btn-secondary dropdown-toggle" type="button" id="user-menu" data-bs-toggle="dropdown" aria-expanded="false">
      <?php echo $sf_user->user->username; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-lg-end mt-2" aria-labelledby="user-menu">
      <li>
        <h6 class="dropdown-header">
          <?php echo image_tag($gravatar, ['alt' => '']); ?>&nbsp;
          <?php echo __('Hi, %1%', ['%1%' => $sf_user->user->username]); ?>
        </h6>
      </li>
      <li><?php echo link_to($menuLabels['myProfile'], [$sf_user->user, 'module' => 'user'], ['class' => 'dropdown-item']); ?></li>
      <li>
        <?php if ($sf_context->getConfiguration()->isPluginEnabled('arCasPlugin')) { ?>
          <?php echo link_to($menuLabels['logout'], ['module' => 'cas', 'action' => 'logout'], ['class' => 'dropdown-item']); ?>
        <?php } elseif ($sf_context->getConfiguration()->isPluginEnabled('arOidcPlugin')) { ?>
          <?php echo link_to($menuLabels['logout'], ['module' => 'oidc', 'action' => 'logout'], ['class' => 'dropdown-item']); ?>
        <?php } ?>
      </li>
    </ul>
  </div>
<?php } ?>
