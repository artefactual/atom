<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Privacy Notification') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'privacyNotification'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Privacy Notification Settings') ?></legend>

        <?php echo $form->privacy_notification_enabled
          ->label(__('Display Privacy Notification on first visit to site'))
          ->renderRow() ?>

        <?php echo get_partial('settings/i18n_form_field',
          array(
            'name' => 'privacy_notification',
            'label' => __('Privacy Notification Message'),
            'settings' => $settings,
            'form' => $form)) ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
