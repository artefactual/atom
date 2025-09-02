<?php decorate_with('layout_1col'); ?>
<?php use_helper('Date'); ?>

<?php slot('title'); ?>
  <div class="multiline-header d-flex flex-column mb-3">
    <h1 class="mb-0" aria-describedby="heading-label">
      <?php echo __('Modifications'); ?>
    </h1>
    <span class="small" id="heading-label">
      <?php echo render_title($resource); ?>
    </span>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Date'); ?>
          </th>
          <th>
            <?php echo __('Type'); ?>
          </th>
          <th>
            <?php echo __('User'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($modifications as $modification) { ?>
          <tr>
            <td>
              <?php echo format_date($modification->createdAt, 'f'); ?>
            </td>
            <td>
              <?php echo QubitTerm::getById($modification->actionTypeId)->getName(['cultureFallback' => true]); ?>
            </td>
            <td>
              <?php echo link_to_if($sf_user->isAdministrator() && $modification->userId, $modification->userName, [QubitUser::getById($modification->userId), 'module' => 'user']); ?>
            </td>
          </tr>
        <?php } ?>
      <tbody>
    </table>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
