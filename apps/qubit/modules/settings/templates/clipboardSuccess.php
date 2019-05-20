<?php decorate_with('layout_2col.php') ?>

<?php slot('sidebar') ?>

  <?php echo get_component('settings', 'menu') ?>

<?php end_slot() ?>

<?php slot('title') ?>

  <h1><?php echo __('Clipboard settings') ?></h1>

<?php end_slot() ?>

<?php slot('content') ?>

  <?php echo $form->renderFormTag(url_for(array('module' => 'settings', 'action' => 'clipboard'))) ?>

    <div id="content">

      <fieldset class="collapsible">

        <legend><?php echo __('Clipboard saving') ?></legend>

        <?php echo $form->clipboard_save_max_age
          ->label(__('Saved clipboard maximum age (in days)'))
          ->help(__('The number of days a saved clipboard should be retained before it is eligible for deletion'))
          ->renderRow() ?>

      </fieldset>

      <fieldset class="collapsible">

        <legend><?php echo __('Clipboard sending') ?></legend>

        <?php echo $form->clipboard_send_enabled
          ->label(__('Enable clipboard send functionality'))
          ->renderRow() ?>

        <?php echo $form->clipboard_send_url
          ->label(__('External URL to send clipboard contents to'))
          ->renderRow() ?>

        <?php echo get_partial('settings/i18n_form_field',
          array(
            'name' => 'clipboard_send_button_text',
            'label' => __('Send button text'),
            'settings' => $settings,
            'form' => $form)) ?>

        <?php echo get_partial('settings/i18n_form_field',
          array(
            'name' => 'clipboard_send_message_html',
            'label' => __('Text or HTML to display when sending clipboard contents'),
            'settings' => $settings,
            'form' => $form)) ?>

        <?php echo $form->clipboard_send_http_method
          ->label(__('HTTP method to use when sending clipboard contents'))
          ->renderRow() ?>

      </fieldset>

    </div>

    <section class="actions">
      <ul>
        <li><input class="c-btn c-btn-submit" type="submit" value="<?php echo __('Save') ?>"/></li>
      </ul>
    </section>

  </form>

<?php end_slot() ?>
