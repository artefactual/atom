<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('I18n language') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <div class="alert alert-info">
    <p><?php echo __('Please rebuild the search index if you are adding new languages.') ?></p>
    <pre>$ php symfony search:populate</pre>
  </div>

  <form action="<?php echo url_for('settings/language') ?>" method="post">

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name')?></th>
            <th><?php echo __('Value')?></th>
            <th><?php echo __('Delete')?></th>
          </tr>
        </thead>
          <tbody>
          <?php foreach ($i18nLanguages as $setting): ?>
            <tr>
              <td>
                <?php echo $setting->getName() ?>
              </td>
              <td>
                <?php echo format_language($setting->getName()) ?>
              </td>
              <td>
                <?php if ($setting->deleteable): ?>
                  <?php echo link_to(image_tag('delete', array('alt' => __('Delete'))), array($setting, 'module' => 'settings', 'action' => 'delete')) ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <tr>
            <td colspan="3">
              <?php echo $form->languageCode->renderRow() ?>
            </td>
          </tr>
        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Add') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
