<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Uploads settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(
    ['module' => 'settings', 'action' => 'uploads']
  )); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <table class="table sticky-enabled">
        <thead>
          <tr>
            <th><?php echo __('Name'); ?></th>
            <th><?php echo __('Value'); ?></th>
          </tr>
        </thead>

        <tbody>

          <?php echo $form
              ->upload_quota
              ->label(__('Total space available for uploads'))
              ->renderRow();
          ?>

          <?php echo $form
              ->enable_repository_quotas
              ->label(
                __('%1% upload limits meter display',
                [
                    '%1%' => sfConfig::get('app_ui_label_repository'),
                ]
              ))
              ->help(__(
                'When enabled, an &quot;Upload limit&quot; meter is displayed for authenticated users on the %1% view page, and administrators can limit the disk space each %1% is allowed for %2% uploads',
                [
                    '%1%' => strtolower(sfConfig::get('app_ui_label_repository')),
                    '%2%' => strtolower(sfConfig::get('app_ui_label_digitalobject')),
                ]
              ))
              ->renderRow();
          ?>

          <?php echo $form
              ->repository_quota
              ->label(__(
              'Default %1% upload limit (GB)',
              ['%1%' => strtolower(sfConfig::get('app_ui_label_repository'))]
            ))
              ->help(__(
                'Default %1% upload limit for a new %2%.  A value of &quot;0&quot; (zero) disables file upload.  A value of &quot;-1&quot; allows unlimited uploads for all %2%s, overriding limit set for individual %2%s.',
                [
                    '%1%' => strtolower(sfConfig::get('app_ui_label_digitalobject')),
                    '%2%' => strtolower(sfConfig::get('app_ui_label_repository')),
                ]
              ))
              ->renderRow();
          ?>

          <?php echo $form
              ->explode_multipage_files
              ->label(__('Upload multi-page files as multiple descriptions'))
              ->renderRow();
          ?>

        </tbody>
      </table>

    </div>

    <section class="actions">
      <ul>
        <li>
          <input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save'); ?>"/>
        </li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
