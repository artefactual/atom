<?php decorate_with('layout_1col'); ?>

<?php slot('title'); ?>
  <h1><?php echo __('List donor'); ?></h1>
<?php end_slot(); ?>

<?php slot('before-content'); ?>
  <div class="nav">
    <div class="search">
      <form action="<?php echo url_for(['module' => 'donor', 'action' => 'list']); ?>">
        <input name="subquery" value="<?php echo $sf_request->subquery; ?>"/>
        <input class="form-submit" type="submit" value="<?php echo __('Search donor'); ?>"/>
      </form>
    </div>
  </div>
<?php end_slot(); ?>

<?php slot('content'); ?>
  <div class="table-responsive mb-3">
    <table class="table table-bordered mb-0">
      <thead>
        <tr>
          <th>
            <?php echo __('Name'); ?>
          </th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($donors as $item) { ?>
          <tr>
            <td>
              <?php echo link_to(render_title($item), [$item, 'module' => 'donor']); ?>
            </td>
          </tr>
        <?php } ?>
      </tbody>
    </table>
  </div>
<?php end_slot(); ?>

<?php slot('after-content'); ?>
  <?php echo get_partial('default/pager', ['pager' => $pager]); ?>
<?php end_slot(); ?>
