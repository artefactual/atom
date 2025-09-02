<div class="table-responsive mb-3 acl-table-container">
  <table class="table table-bordered mb-0 caption-top" id="<?php echo 'acl_'.$object->slug; ?>">
    <caption class="pt-0">
      <span class="d-inline-block">
        <?php if ($object->id != constant(get_class($sf_data->getRaw('object')).'::ROOT_ID')) { ?>
          <?php echo render_title($object); ?>
        <?php } else { ?>
          <em>
            <?php echo __(
                'All %1%',
                ['%1%' => lcfirst(sfConfig::get('app_ui_label_'.$module))]
            ); ?>
          </em>
        <?php } ?>
      </span>
    </caption>
    <thead class="table-light">
      <tr>
        <th scope="col"><?php echo __('Action'); ?></th>
        <th scope="col"><?php echo __('Permission'); ?></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($actions as $key => $item) { ?>
        <tr>
          <td><?php echo __($item); ?></td>
          <td id="<?php echo $module.'_'.$object->id.'_'.$key; ?>">
            <?php if (isset($permissions[$key])) { ?>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="acl[<?php echo $permissions[$key]->id; ?>]"
                  id="acl_grant_[<?php echo $permissions[$key]->id; ?>]"
                  <?php echo (1 == $permissions[$key]->grantDeny) ? 'checked' : ''; ?>
                  value="<?php echo QubitAcl::GRANT; ?>">
                <label class="form-check-label" for="acl_grant_[<?php echo $permissions[$key]->id; ?>]">
                  <?php echo __('Grant'); ?>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="acl[<?php echo $permissions[$key]->id; ?>]"
                  id="acl_deny_[<?php echo $permissions[$key]->id; ?>]"
                  <?php echo (0 == $permissions[$key]->grantDeny) ? 'checked' : ''; ?>
                  value="<?php echo QubitAcl::DENY; ?>">
                <label class="form-check-label" for="acl_deny_[<?php echo $permissions[$key]->id; ?>]">
                  <?php echo __('Deny'); ?>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="acl[<?php echo $permissions[$key]->id; ?>]"
                  id="acl_inherit_[<?php echo $permissions[$key]->id; ?>]"
                  value="<?php echo QubitAcl::INHERIT; ?>">
                <label class="form-check-label" for="acl_inherit_[<?php echo $permissions[$key]->id; ?>]">
                  <?php echo __('Inherit'); ?>
                </label>
              </div>
            <?php } else { ?>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="acl[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  id="acl_grant_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  value="<?php echo QubitAcl::GRANT; ?>">
                <label
                  class="form-check-label"
                  for="acl_grant_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]">
                  <?php echo __('Grant'); ?>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  name="acl[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  id="acl_deny_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  value="<?php echo QubitAcl::DENY; ?>">
                <label
                  class="form-check-label"
                  for="acl_deny_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]">
                  <?php echo __('Deny'); ?>
                </label>
              </div>
              <div class="form-check form-check-inline">
                <input
                  class="form-check-input"
                  type="radio"
                  checked
                  name="acl[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  id="acl_inherit_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]"
                  value="<?php echo QubitAcl::INHERIT; ?>">
                <label
                  class="form-check-label"
                  for="acl_inherit_[<?php echo $key.'_'.url_for([$object, 'module' => $module]); ?>]">
                  <?php echo __('Inherit'); ?>
                </label>
              </div>
            <?php } ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>
