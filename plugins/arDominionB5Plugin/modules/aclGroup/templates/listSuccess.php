<h1><?php echo __('List groups'); ?></h1>

<div class="table-responsive mb-3">
  <table class="table table-bordered mb-0">
    <thead>
      <tr>
        <th>
          <?php echo __('Group'); ?>
        </th><th>
          <?php echo __('Members'); ?>
        </th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pager->getResults() as $item) { ?>
        <tr>
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
</div>

<?php echo get_partial('default/pager', ['pager' => $pager]); ?>

<section class="actions mb-3">
  <?php echo link_to(__('Add new'), ['module' => 'aclGroup', 'action' => 'add'], ['class' => 'btn atom-btn-outline-light']); ?>
</div>
