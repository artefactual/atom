<h1><?php echo __('Physical storage'); ?></h1>

<h1 class="label"><?php echo render_title($resource); ?></h1>

<table class="sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Name'); ?>
      </th><th>
        <?php echo __('Location'); ?>
      </th><th>
        <?php echo __('Type'); ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($physicalObjects as $item) { ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
        <td>
          <?php echo link_to(render_title($item), [$item, 'module' => 'physicalobject']); ?>
        </td><td>
          <?php echo render_value($item->getLocation(['cultureFallback' => true])); ?>
        </td><td>
          <?php echo render_value($item->type); ?>
        </td>
      </tr>
    <?php } ?>
  <tbody>
</table>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>
