<?php if ($showLogin) { ?>
  <div class="dropdown my-2 my-lg-0">
    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="user-menu" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
      <?php echo $menuLabels['login']; ?>
    </button>
    <div class="dropdown-menu dropdown-menu-lg-end" aria-labelledby="user-menu">
      <li>
        <h5 class="dropdown-item-text">
          <?php echo __('Have an account?'); ?>
        </h5>
      </li>
      <li><hr class="dropdown-divider"></li>
      <?php echo $form->renderFormTag(url_for(['module' => 'user', 'action' => 'login']), ['class' => 'mx-3 my-2']); ?>
        <?php echo $form->renderHiddenFields(); ?>
        <?php echo $form->email->renderRow(['class' => 'form-control form-control-sm mb-3']); ?>
        <?php echo $form->password->renderRow(['class' => 'form-control form-control-sm mb-3', 'autocomplete' => 'off']); ?>
        <button class="btn btn-sm btn-secondary" type="submit">
          <?php echo $menuLabels['login']; ?>
        </button>
      </form>
    </div>
  </div>
<?php } elseif ($sf_user->isAuthenticated()) { ?>
  <div class="dropdown my-2 my-lg-0">
    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="user-menu" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
      <?php echo $sf_user->user->username; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-lg-end" aria-labelledby="user-menu">
      <li>
        <h5 class="dropdown-item-text">
          <?php echo image_tag($gravatar, ['alt' => '']); ?>&nbsp;
          <?php echo __('Hi, %1%', ['%1%' => $sf_user->user->username]); ?>
        </h5>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li><?php echo link_to($menuLabels['myProfile'], [$sf_user->user, 'module' => 'user'], ['class' => 'dropdown-item']); ?></li>
      <li><?php echo link_to($menuLabels['logout'], ['module' => 'user', 'action' => 'logout'], ['class' => 'dropdown-item']); ?></li>
    </ul>
  </div>
<?php } ?>
