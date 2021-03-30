<h1><?php echo __('List groups'); ?></h1>

<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Group'); ?>
      </th><th>
        <?php echo __('Members'); ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item) { ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd'; ?>">
        <td>
          <?php if ($item->isProtected()) { ?>
            <?php echo link_to($item->getName(['cultureFallback' => true]), [$item, 'module' => 'aclGroup'], ['class' => 'readOnly']); ?>
          <?php } else { ?>
            <?php echo link_to($item->getName(['cultureFallback' => true]), [$item, 'module' => 'aclGroup']); ?>
          <?php } ?>
        </td><td>
          <?php echo count($item->aclUserGroups); ?>
        </td>
      </tr>
    <?php } ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<section class="actions">
  <ul>
    <li><?php echo link_to(__('Add new'), ['module' => 'aclGroup', 'action' => 'add'], ['class' => 'c-btn']); ?></li>
  </ul>
</div>
