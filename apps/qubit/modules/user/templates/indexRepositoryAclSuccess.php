<h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>

<?php echo get_component('user', 'aclMenu'); ?>

<div class="section">

  <?php if (0 < count($acl)) { ?>

    <table id="userPermissions" class="table table-bordered sticky-enabled">
      <thead>
        <tr>
          <th colspan="2">&nbsp;</th>
          <?php foreach ($userGroups as $item) { ?>
            <?php if (null !== $group = QubitAclGroup::getById($item)) { ?>
              <th><?php echo esc_entities($group->__toString()); ?></th>
            <?php } elseif ($resource->username == $item) { ?>
              <th><?php echo $resource->username; ?></th>
            <?php } ?>
          <?php } ?>
        </tr>
      </thead><tbody>
        <?php foreach ($acl as $objectId => $actions) { ?>
          <tr>
            <td colspan="<?php echo $tableCols; ?>"><strong>
              <?php if (1 < $objectId) { ?>
                <?php echo esc_entities(render_title(QubitRepository::getById($objectId))); ?>
              <?php } else { ?>
                <em><?php echo __('All %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_repository'))]); ?></em>
              <?php } ?>
            </strong></td>
          </tr>
          <?php foreach ($actions as $action => $groupPermission) { ?>
            <tr>
              <td>&nbsp;</td>
              <td>
                <?php if ('' != $action) { ?>
                  <?php echo QubitAcl::$ACTIONS[$action]; ?>
                <?php } else { ?>
                  <em><?php echo __('All privileges'); ?></em>
                <?php } ?>
              </td>
              <?php foreach ($sf_data->getRaw('userGroups') as $groupId) { ?>
                <td>
                  <?php if (isset($groupPermission[$groupId]) && $permission = $groupPermission[$groupId]) { ?>
                    <?php if ('translate' == $permission->action && null !== $permission->getConstants(['name' => 'languages'])) { ?>
                      <?php $permission = sfOutputEscaper::unescape($permission); ?>
                      <?php echo $permission->renderAccess().': '.implode(',', $permission->getConstants(['name' => 'languages'])); ?>
                    <?php } else { ?>
                      <?php echo $permission->renderAccess(); ?>
                    <?php } ?>
                  <?php } else { ?>
                    <?php echo '-'; ?>
                  <?php } ?>
                </td>
              <?php } ?>
            </tr>
          <?php } ?>
        <?php } ?>
      </tbody>
    </table>

  <?php } ?>

</div>

<?php echo get_partial('showActions', ['resource' => $resource]); ?>
