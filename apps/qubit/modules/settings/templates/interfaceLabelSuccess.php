<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('User interface label') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <form action="<?php echo url_for('settings/interfaceLabel') ?>" method="post">

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($uiLabelForm->getSettings() as $setting): ?>
            <tr>
              <td>
                <?php if ($sf_user->getCulture() != $setting->getSourceCulture() && !strlen($setting->getValue())): ?>
                  <div class="default-translation"><?php echo $setting->getName() ?></div>
                <?php else: ?>
                  <?php echo $setting->getName() ?>
                <?php endif; ?>
              </td>
              <td>
                <?php echo $uiLabelForm[$setting->getName()] ?>
              </td>
            </tr>
          <?php endforeach; ?>
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
