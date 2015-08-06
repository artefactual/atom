<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Default template') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/template') ?>" method="post">

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $defaultTemplateForm ?>
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
