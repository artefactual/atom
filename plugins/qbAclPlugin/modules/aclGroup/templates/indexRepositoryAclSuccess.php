<?php echo get_component('aclGroup', 'tabs') ?>

<h1><?php echo __('View permissions') ?></h1>

<h1 class="label"><?php echo link_to_if(QubitAcl::check($group, 'edit'), render_title($group), array($group, 'module' => 'aclGroup', 'action' => 'edit'), array('title' => __('Edit group'))) ?></h1>

<div class="section">
  <?php if (0 < count($acl)): ?>
  <table id="userPermissions">
    <thead>
      <tr>
      <th colspan="2">&nbsp;</th>
      <?php foreach ($roles as $roleId): ?>
        <th><?php echo QubitAclGroup::getById($roleId)->__toString() ?></th>
      <?php endforeach; ?>
      </tr>
    </thead>

    <tbody>
    <?php foreach ($acl as $objectId => $actions): ?>
      <tr>
        <td colspan="<?php echo $tableCols - 1 ?>"><strong>
        <?php if (1 < $objectId): ?>
          <?php echo render_title(QubitRepository::getById($objectId)) ?>
        <?php else: ?>
          <em><?php echo __('All %1%', array('%1%' => sfConfig::get('app_ui_label_repository'))) ?></em>
        <?php endif; ?>
        </strong></td>
      </tr>

    <?php $row = 0; ?>
    <?php foreach ($actions as $action => $groupPermission): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
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
