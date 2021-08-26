<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Security settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <div class="alert alert-info">
    <?php echo __('Note: Incorrect security settings can result in the AtoM web UI becoming inaccessible.'); ?>
  </div>

  <form action="<?php echo url_for('settings/security'); ?>" method="post">

    <div class="table-responsive mb-3">
      <table class="table table-bordered mb-0">
        <thead>
          <tr>
            <th width="30%"><?php echo __('Name'); ?></th>
            <th><?php echo __('Value'); ?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $form; ?>
        </tbody>
      </table>
    </div>

    <section class="actions">
      <input class="btn atom-btn-outline-success" type="submit" value="<?php echo __('Save'); ?>">
    </section>

  </form>

<?php end_slot(); ?>
