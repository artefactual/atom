<?php echo get_component('aclGroup', 'tabs') ?>

<h1><?php echo __('View permissions') ?></h1>

<h1 class="label"><?php echo link_to_if(QubitAcl::check($group, 'edit'), render_title($group), array($group, 'module' => 'aclGroup', 'action' => 'edit'), array('title' => __('Edit group'))) ?></h1>

<div class="section">
  <?php if (0 < count($acl)): ?>
  <table id="groupPermissions" class="sticky-enabled">
    <thead>
      <tr>
      <th colspan="2">&nbsp;</th>
      <?php foreach ($groups as $groupId): ?>
        <th><?php echo render_title(QubitAclGroup::getById($groupId)) ?></th>
      <?php endforeach; ?>
      </tr>
    </thead>

    <tbody>
    <?php foreach ($acl as $taxonomy => $actions): ?>
      <tr>
        <td colspan="<?php echo $tableCols ?>"><strong>
        <?php if ('' == $taxonomy): ?>
          <em><?php echo __('All %1%', array('%1%' => sfConfig::get('app_ui_label_term'))) ?></em>
        <?php else: ?>
          <?php echo __('Taxonomy: %1%', array('%1%' => render_title(QubitTaxonomy::getBySlug($taxonomy)))) ?>
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

        <?php foreach ($groups as $groupId): ?>
        <td>
        <?php if (isset($groupPermission[$groupId]) && $permission = $groupPermission[$groupId]): ?>
          <?php if ('translate' == $permission->action && null !== $permission->getConstants(array('name' => 'languages'))): ?>
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
    </tbody>
  </table>
<?php endif; ?>

</div>

<?php echo get_partial('showActions', array('group' => $group)) ?>
