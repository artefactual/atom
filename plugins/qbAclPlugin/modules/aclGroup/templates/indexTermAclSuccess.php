<h1><?php echo __('Group %1%', ['%1%' => render_title($group)]); ?></h1>

<?php echo get_component('aclGroup', 'tabs'); ?>

<?php if (0 < count($acl)) { ?>
  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th colspan="2">&nbsp;</th>
        <?php foreach ($groups as $groupId) { ?>
          <th><?php echo esc_entities(render_title(QubitAclGroup::getById($groupId))); ?></th>
        <?php } ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($acl as $taxonomy => $actions) { ?>
        <tr>
          <td colspan="<?php echo $tableCols; ?>">
            <strong>
              <?php if ('' == $taxonomy) { ?>
                <em><?php echo __('All %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_term'))]); ?></em>
              <?php } else { ?>
                <?php echo __('Taxonomy: %1%', ['%1%' => esc_entities(render_title(QubitTaxonomy::getBySlug($taxonomy)))]); ?>
              <?php } ?>
            </strong>
          </td>
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
            <?php foreach ($groups as $groupId) { ?>
              <td>
                <?php if (isset($groupPermission[$groupId]) && $permission = $groupPermission[$groupId]) { ?>
                  <?php if ('translate' == $permission->action && null !== $permission->getConstants(['name' => 'languages'])) { ?>
                    <?php $permission = sfOutputEscaper::unescape($permission); ?>
                    <?php echo __('%1%: %2%', ['%1%' => $permission->renderAccess(), '%2%' => implode(', ', $permission->getConstants(['name' => 'languages']))]); ?>
                  <?php } else { ?>
                    <?php echo __($permission->renderAccess()); ?>
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

<?php echo get_partial('showActions', ['group' => $group]); ?>
