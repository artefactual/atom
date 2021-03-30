<h1><?php echo __('Search %1%', ['%1%' => sfConfig::get('app_ui_label_actor')]); ?></h1>

<?php if (isset($error)) { ?>

  <div class="search-results">
    <ul>
      <li><?php echo $error; ?></li>
    </ul>
  </div>

<?php } else { ?>

  <table class="sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name'); ?>
        </th><th>
          <?php echo __('Type'); ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($actors as $item) { ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
          <td>
            <?php echo link_to(render_title($item), [$item, 'module' => 'actor']); ?>
          </td><td>
            <?php echo render_value_inline($item->entityType); ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<?php } ?>
