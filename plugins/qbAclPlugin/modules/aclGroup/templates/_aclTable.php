<table id="<?php echo 'acl_'.url_for(array($object, 'module' => $module)) ?>" class="table table-bordered">
  <?php if ($object->id != constant(get_class($sf_data->getRaw('object')).'::ROOT_ID')): ?>
    <caption><?php echo render_title($object) ?></caption>
  <?php else: ?>
    <caption><em><?php echo __('All %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_'.$module)))) ?></em></caption>
  <?php endif; ?>
  <thead>
    <tr>
      <th scope="col"><?php echo __('Action') ?></th>
      <th scope="col"><?php echo __('Permission') ?></th>
    </tr>
  </thead><tbody>
    <?php foreach ($actions as $key => $item): ?>
      <tr>
        <td><?php echo __($item) ?></td>
        <td id="<?php echo $module.'_'.$object->id.'_'.$key ?>">
          <ul class="radio inline">
            <?php if (isset($permissions[$key])): ?>
              <li><input type="radio" name="acl[<?php echo $permissions[$key]->id ?>]" value="<?php echo QubitAcl::GRANT ?>"<?php echo (1 == $permissions[$key]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Grant') ?></li>
              <li><input type="radio" name="acl[<?php echo $permissions[$key]->id ?>]" value="<?php echo QubitAcl::DENY ?>"<?php echo (0 == $permissions[$key]->grantDeny) ? ' checked="checked"' : '' ?>><?php echo __('Deny') ?></li>
              <li><input type="radio" name="acl[<?php echo $permissions[$key]->id ?>]" value="<?php echo QubitAcl::INHERIT?>"><?php echo __('Inherit') ?></li>
            <?php else: ?>
              <li><input type="radio" name="acl[<?php echo $key.'_'.url_for(array($object, 'module' => $module)) ?>]" value="<?php echo QubitAcl::GRANT ?>"><?php echo __('Grant') ?></li>
              <li><input type="radio" name="acl[<?php echo $key.'_'.url_for(array($object, 'module' => $module)) ?>]" value="<?php echo QubitAcl::DENY ?>"><?php echo __('Deny') ?></li>
              <li><input type="radio" name="acl[<?php echo $key.'_'.url_for(array($object, 'module' => $module)) ?>]" value="<?php echo QubitAcl::INHERIT ?>" checked="checked"><?php echo __('Inherit') ?></li>
            <?php endif; ?>
          </ul>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
