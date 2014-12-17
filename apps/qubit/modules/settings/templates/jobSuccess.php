<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_partial('settings/menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Job scheduling') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/job') ?>" method="post">

    <p><?php echo __('Specific Gearman job server options can be found in config/gearman.yml.') ?></p>

    <div id="content">

      <table class="table">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $jobSchedulingForm ?>
        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
