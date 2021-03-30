<?php decorate_with('layout_2col.php'); ?>

<?php slot('sidebar'); ?>

  <?php echo get_component('settings', 'menu'); ?>

<?php end_slot(); ?>

<?php slot('title'); ?>

  <h1><?php echo __('Storage Service settings'); ?></h1>

<?php end_slot(); ?>

<?php slot('content'); ?>

  <?php echo $form->renderGlobalErrors(); ?>

  <?php echo $form->renderFormTag(url_for(
    ['module' => 'arStorageServiceSettings', 'action' => 'settings']
  )); ?>

    <?php echo $form->renderHiddenFields(); ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Storage Service credentials'); ?></legend>

        <?php echo $form->storage_service_api_url
            ->label(__(
            'Storage Service API endpoint, e.g. "http://localhost:62081/api/v2"'
          ))
            ->renderRow(); ?>

        <?php echo $form->storage_service_username
            ->label(__('Storage Service username, e.g. "atom"'))
            ->renderRow(); ?>

        <?php echo $form->storage_service_api_key
            ->label(__(
            'Storage Service API key, e.g.'
            .'"2ef7bde608ce5404e97d5f042f95f89f1c232871"'
          ))
            ->renderRow(); ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('AIP download'); ?></legend>

        <?php echo $form->download_aip_enabled
            ->label(__('Enable AIP download'))
            ->help(__(
            'Allow authorized users to download a linked AIP or AIP file from'
            .' the configured Archivematica Storage Service'
          ))
            ->renderRow(); ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li>
          <input class="c-btn c-btn-submit" type="submit"
            value="<?php echo __('Save'); ?>"
          />
        </li>
      </ul>
    </section>

  </form>

<?php end_slot(); ?>
