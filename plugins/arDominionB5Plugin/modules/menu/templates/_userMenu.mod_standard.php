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
      <?php echo $form->renderFormTag(url_for(['module' => 'user', 'action' => 'login']), ['class' => 'mx-3 my-2']); ?>
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo render_field($form->email, null, ['class' => 'form-control-sm']); ?>
        <?php echo render_field($form->password, null, ['class' => 'form-control-sm', 'autocomplete' => 'off']); ?>
        <button class="btn btn-sm atom-btn-secondary" type="submit">
          <?php echo $menuLabels['login']; ?>
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
      <li><?php echo link_to($menuLabels['logout'], ['module' => 'user', 'action' => 'logout'], ['class' => 'dropdown-item']); ?></li>
    </ul>
  </div>
<?php } ?>
