<h1><?php echo __('List groups') ?></h1>

<table class="table table-bordered sticky-enabled">
  <thead>
    <tr>
      <th>
        <?php echo __('Group') ?>
      </th><th>
        <?php echo __('Members') ?>
      </th>
    </tr>
  </thead><tbody>
    <?php foreach ($pager->getResults() as $item): ?>
      <tr class="<?php echo 0 == @++$row % 2 ? 'even' : 'odd' ?>">
        <td>
          <?php if ($item->isProtected()): ?>
            <?php echo link_to($item->getName(array('cultureFallback' => true)), array($item, 'module' => 'aclGroup'), array('class' => 'readOnly')) ?>
          <?php else: ?>
            <?php echo link_to($item->getName(array('cultureFallback' => true)), array($item, 'module' => 'aclGroup')) ?>
          <?php endif; ?>
        </td><td>
          <?php echo count($item->aclUserGroups) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<?php echo get_partial('default/pager', array('pager' => $pager)) ?>

<section class="actions">
  <ul>
    <li><?php echo link_to(__('Add new'), array('module' => 'aclGroup', 'action' => 'add'), array('class' => 'c-btn')) ?></li>
  </ul>
</div>
