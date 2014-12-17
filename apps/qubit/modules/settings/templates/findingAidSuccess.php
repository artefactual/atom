<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_partial('settings/menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Finding Aid settings') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/findingAid') ?>" method="post">

    <div id="content">

      <table class="table">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $findingAidForm ?>
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
