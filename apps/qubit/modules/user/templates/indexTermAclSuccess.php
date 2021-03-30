<h1><?php echo __('User %1%', ['%1%' => render_title($resource)]); ?></h1>

<?php echo get_component('user', 'aclMenu'); ?>

<div class="section">

  <?php if (0 < count($acl)) { ?>

    <table id="userPermissions" class="table table-bordered sticky-enabled">
      <thead>
        <tr>
          <th colspan="2">&nbsp;</th>
          <?php foreach ($roles as $item) { ?>
            <?php if (null !== $group = QubitAclGroup::getById($item)) { ?>
              <th><?php echo esc_entities($group->__toString()); ?></th>
            <?php } elseif ($resource->username == $item) { ?>
              <th><?php echo $resource->username; ?></th>
            <?php } ?>
          <?php } ?>
        </tr>
      </thead><tbody>
        <?php foreach ($acl as $taxonomy => $actions) { ?>
          <tr>
            <td colspan="<?php echo $tableCols; ?>"><strong>
              <?php if ('' == $taxonomy) { ?>
                <em><?php echo __('All %1%', ['%1%' => lcfirst(sfConfig::get('app_ui_label_term'))]); ?></em>
              <?php } else { ?>
                <?php echo __('Taxonomy: %1%', ['%1%' => esc_entities(render_title(QubitTaxonomy::getBySlug($taxonomy)))]); ?>
              <?php } ?>
            </strong></td>
          </tr>
          <?php $row = 0; ?>
          <?php foreach ($actions as $action => $groupPermission) { ?>
            <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
              <td>&nbsp;</td>
              <td>
                <?php if ('' != $action) { ?>
                  <?php echo QubitAcl::$ACTIONS[$action]; ?>
                <?php } else { ?>
                  <em><?php echo __('All privileges'); ?></em>
                <?php } ?>
              </td>
              <?php foreach ($sf_data->getRaw('roles') as $roleId) { ?>
                <td>
                  <?php if (isset($groupPermission[$roleId]) && $permission = $groupPermission[$roleId]) { ?>
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
