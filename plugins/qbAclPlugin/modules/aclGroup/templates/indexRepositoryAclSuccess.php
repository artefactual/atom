<h1><?php echo __('Group %1%', array('%1%' => render_title($group))) ?></h1>

<?php echo get_component('aclGroup', 'tabs') ?>

<div class="section">

  <?php if (0 < count($acl)): ?>

    <table id="userPermissions" class="table table-bordered">
      <thead>
        <tr>
        <th colspan="2">&nbsp;</th>
        <?php foreach ($roles as $roleId): ?>
          <th><?php echo esc_entities(render_title(QubitAclGroup::getById($roleId))) ?></th>
        <?php endforeach; ?>
        </tr>
      </thead><tbody>
        <?php foreach ($acl as $objectId => $actions): ?>
          <tr>
            <td colspan="<?php echo $tableCols - 1 ?>"><strong>
            <?php if (1 < $objectId): ?>
              <?php echo esc_entities(render_title(QubitRepository::getById($objectId))) ?>
            <?php else: ?>
              <em><?php echo __('All %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_repository')))) ?></em>
            <?php endif; ?>
            </strong></td>
          </tr>
          <?php foreach ($actions as $action => $groupPermission): ?>
            <tr>
              <td>&nbsp;</td>
              <td>
                <?php if ('' != $action): ?>
                  <?php echo QubitAcl::$ACTIONS[$action] ?>
                <?php else: ?>
                  <em><?php echo __('All privileges') ?></em>
                <?php endif; ?>
              </td>
              <?php foreach ($roles as $roleId): ?>
              <td>
                <?php if (isset($groupPermission[$roleId]) && $permission = $groupPermission[$roleId]): ?>
                  <?php echo __($permission->renderAccess()) ?>
                <?php else: ?>
                  <?php echo '-' ?>
                <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </tbody>
    </table>

  <?php endif; ?>

</div>

<?php echo get_partial('showActions', array('group' => $group)) ?>
