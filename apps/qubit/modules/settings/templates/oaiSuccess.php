<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('OAI repository settings') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/oai') ?>" method="post">

    <p><?php echo __('The OAI-PMH API can be secured, optionally, by requiring API requests authenticate using API keys (granted to specific users).') ?></p>

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th width="30%"><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <?php echo $oaiRepositoryForm ?>
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
