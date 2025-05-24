<?php if ($showLogin) { ?>

  <div id="user-menu">
    <button class="top-item top-dropdown" data-toggle="dropdown" data-target="#"
      aria-expanded="false">
        <?php echo $menuLabels['login']; ?>
    </button>

    <div class="top-dropdown-container">

      <div class="top-dropdown-arrow">
        <div class="arrow"></div>
      </div>

      <div class="top-dropdown-header">
        <h2><?php echo __('Have an account?'); ?></h2>
      </div>

      <div class="top-dropdown-body">

        <?php echo $form->renderFormTag(url_for(['module' => 'oidc', 'action' => 'login'])); ?>

          <?php echo $form->renderHiddenFields(); ?>

          <button type="submit"><?php echo __('Log in with SSO'); ?></button>

        </form>

      </div>

      <div class="top-dropdown-bottom"></div>

    </div>
  </div>

<?php } elseif ($sf_user->isAuthenticated()) { ?>

  <div id="user-menu">

    <button class="top-item top-dropdown" data-toggle="dropdown" data-target="#" aria-expanded="false">
      <?php echo $sf_user->user->username; ?>
    </button>

    <div class="top-dropdown-container">

      <div class="top-dropdown-arrow">
        <div class="arrow"></div>
      </div>

      <div class="top-dropdown-header">
        <?php echo image_tag($gravatar, ['alt' => '']); ?>&nbsp;
        <h2><?php echo __('Hi, %1%', ['%1%' => $sf_user->user->username]); ?></h2>
      </div>

      <div class="top-dropdown-body">

        <ul>
          <li><?php echo link_to($menuLabels['myProfile'], [
              $sf_user->user, 'module' => 'user', ]); ?></li>
          <li><?php echo link_to($menuLabels['logout'], [
              'module' => 'oidc', 'action' => 'logout', ]); ?></li>
        </ul>

      </div>

      <div class="top-dropdown-bottom"></div>

    </div>

  </div>

<?php } ?>
