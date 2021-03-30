<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('List taxonomies'); ?></h1>
<?php end_slot(); ?>

<?php slot('content'); ?>

  <table class="table table-bordered sticky-enabled">
    <thead>
      <tr>
        <th>
          <?php echo __('Name'); ?>
        </th><th>
          <?php echo __('Note'); ?>
        </th>
      </tr>
    </thead><tbody>
      <?php foreach ($taxonomies as $item) { ?>
        <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
          <td>
            <?php echo link_to(render_title($item), [$item, 'module' => 'taxonomy']); ?>
          </td><td>
            <?php echo render_value_inline($item->getNote(['cultureFallback' => true])); ?>
          </td>
        </tr>
      <?php } ?>
    </tbody>
  </table>

<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
