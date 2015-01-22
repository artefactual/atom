<h1><?php echo __('Group %1%', array('%1%' => render_title($group))) ?></h1>

<?php echo get_component('aclGroup', 'tabs') ?>

<?php if (0 < count($acl)): ?>
  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th colspan="2">&nbsp;</th>
        <?php foreach ($groups as $groupId): ?>
          <th><?php echo esc_entities(render_title(QubitAclGroup::getById($groupId))) ?></th>
        <?php endforeach; ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($acl as $repository => $objects): ?>
        <?php foreach ($objects as $objectId => $actions): ?>
          <tr>
            <td colspan="<?php echo $tableCols ?>">
              <strong>
                <?php if ('' == $repository && '' == $objectId): ?>
                  <em><?php echo __('All %1%', array('%1%' => lcfirst(sfConfig::get('app_ui_label_informationobject')))) ?></em>
                <?php elseif ('' != $repository): ?>
                  <?php echo __('%1%: %2%', array('%1%' => sfConfig::get('app_ui_label_repository'), '%2%' => render_title(QubitRepository::getBySlug($repository)))) ?>
                <?php else: ?>
                  <?php echo render_title(QubitInformationObject::getById($objectId)) ?>
                <?php endif; ?>
              </strong>
            </td>
          </tr>
          <?php foreach ($actions as $action => $groupPermission): ?>
            <tr>
              <td>&nbsp;</td>
              <td>
                <?php if ('' != $action): ?>
                  <?php echo QubitInformationObjectAcl::$ACTIONS[$action] ?>
                <?php else: ?>
                  <em><?php echo __('All privileges') ?></em>
                <?php endif; ?>
              </td>
              <?php foreach ($sf_data->getRaw('groups') as $groupId): ?>
                <td>
                  <?php if (isset($groupPermission[$groupId]) && $permission = $groupPermission[$groupId]): ?>
                    <?php if ('translate' == $permission->action && null !== $permission->getConstants(array('name' => 'languages'))): ?>
                      <?php $permission = sfOutputEscaper::unescape($permission) ?>
                      <?php echo __('%1%: %2%', array('%1%' => $permission->renderAccess(), '%2%' => implode(', ', $permission->getConstants(array('name' => 'languages'))))) ?>
                    <?php else: ?>
                      <?php echo __($permission->renderAccess()) ?>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php echo '-' ?>
                  <?php endif; ?>
                </td>
              <?php endforeach; ?>
            </tr>
          <?php endforeach; ?>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php echo get_partial('showActions', array('group' => $group)) ?>
